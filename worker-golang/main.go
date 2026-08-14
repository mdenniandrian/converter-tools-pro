package main

import (
	"archive/zip"
	"compress/flate"
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"os"
	"os/exec"
	"os/signal"
	"path/filepath"
	"strconv"
	"strings"
	"syscall"
	"time"

	_ "github.com/lib/pq"
	"github.com/minio/minio-go/v7"
	"github.com/minio/minio-go/v7/pkg/credentials"
	"github.com/redis/go-redis/v9"
)

// JobPayload represents the JSON payload pushed from Laravel to Redis
type JobPayload struct {
	JobID        string `json:"job_id"`
	ActionType   string `json:"action_type"`
	InputS3Key   string `json:"input_s3_key"`
	OutputS3Key  string `json:"output_s3_key"`
	TargetFormat string `json:"target_format"`
	CreatedAt    string `json:"created_at"`
}

// Config holds environment parameters
type Config struct {
	RedisHost    string
	RedisPort    string
	RedisQueue   string
	DBHost       string
	DBPort       string
	DBUser       string
	DBPassword   string
	DBName       string
	S3Endpoint   string
	S3AccessKey  string
	S3SecretKey  string
	S3Bucket     string
	S3UseSSL     bool
	MaxWorkers   int
}

func loadConfig() Config {
	maxW, _ := strconv.Atoi(getEnv("MAX_WORKERS", "4"))
	if maxW <= 0 {
		maxW = 4
	}

	return Config{
		RedisHost:   getEnv("REDIS_HOST", "localhost"),
		RedisPort:   getEnv("REDIS_PORT", "6379"),
		RedisQueue:  getEnv("REDIS_QUEUE_KEY", "converter_jobs_doc"),
		DBHost:      getEnv("DB_HOST", "localhost"),
		DBPort:      getEnv("DB_PORT", "5432"),
		DBUser:      getEnv("DB_USER", "converter_user"),
		DBPassword:  getEnv("DB_PASSWORD", "secret123"),
		DBName:      getEnv("DB_NAME", "converter_db"),
		S3Endpoint:  getEnv("S3_ENDPOINT", "localhost:9000"),
		S3AccessKey: getEnv("S3_ACCESS_KEY", "minioadmin"),
		S3SecretKey: getEnv("S3_SECRET_KEY", "minioadmin123"),
		S3Bucket:    getEnv("S3_BUCKET", "temp-converter-files"),
		S3UseSSL:    getEnv("S3_USE_SSL", "false") == "true",
		MaxWorkers:  maxW,
	}
}

func getEnv(key, fallback string) string {
	if val, ok := os.LookupEnv(key); ok && val != "" {
		return val
	}
	return fallback
}

func main() {
	log.Println("[Golang Worker] Starting Document, Image & Compression Worker...")

	cfg := loadConfig()

	// 1. Initialize Redis Client
	rdb := redis.NewClient(&redis.Options{
		Addr: fmt.Sprintf("%s:%s", cfg.RedisHost, cfg.RedisPort),
	})

	// 2. Initialize MinIO Client
	minioClient, err := minio.New(cfg.S3Endpoint, &minio.Options{
		Creds:  credentials.NewStaticV4(cfg.S3AccessKey, cfg.S3SecretKey, ""),
		Secure: cfg.S3UseSSL,
	})
	if err != nil {
		log.Fatalf("[Golang Worker] Failed to initialize MinIO client: %v", err)
	}

	// 3. Initialize Database Connection (PostgreSQL)
	dsn := fmt.Sprintf("host=%s port=%s user=%s password=%s dbname=%s sslmode=disable",
		cfg.DBHost, cfg.DBPort, cfg.DBUser, cfg.DBPassword, cfg.DBName)
	db, err := sql.Open("postgres", dsn)
	if err != nil {
		log.Fatalf("[Golang Worker] Failed to connect to DB: %v", err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		log.Fatalf("[Golang Worker] Database unreachable: %v", err)
	}

	log.Printf("[Golang Worker] Connected to Redis, MinIO, and PostgreSQL. Max Concurrency: %d workers\n", cfg.MaxWorkers)

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, os.Interrupt, syscall.SIGTERM)

	go func() {
		<-sigChan
		log.Println("[Golang Worker] Signal received. Shutting down worker pool...")
		cancel()
	}()

	workerSem := make(chan struct{}, cfg.MaxWorkers)

	for {
		select {
		case <-ctx.Done():
			log.Println("[Golang Worker] Main loop terminated. Exiting.")
			return
		default:
			result, err := rdb.BLPop(ctx, 3*time.Second, cfg.RedisQueue).Result()
			if err != nil {
				if err == redis.Nil || strings.Contains(err.Error(), "context canceled") {
					continue
				}
				log.Printf("[Golang Worker] Redis BLPop error: %v", err)
				time.Sleep(1 * time.Second)
				continue
			}

			if len(result) < 2 {
				continue
			}

			payloadJSON := result[1]
			var job JobPayload
			if err := json.Unmarshal([]byte(payloadJSON), &job); err != nil {
				log.Printf("[Golang Worker] Error parsing JSON payload: %v", err)
				continue
			}

			workerSem <- struct{}{}

			go func(j JobPayload) {
				defer func() { <-workerSem }()
				processJob(ctx, db, minioClient, cfg, j)
			}(job)
		}
	}
}

func processJob(ctx context.Context, db *sql.DB, minioClient *minio.Client, cfg Config, job JobPayload) {
	log.Printf("[Worker Goroutine] Processing Job ID: %s (Format: %s)\n", job.JobID, job.TargetFormat)

	updateJobStatus(db, job.JobID, "processing", "")

	tempDir, err := os.MkdirTemp("", fmt.Sprintf("doc-conv-%s-*", job.JobID))
	if err != nil {
		failJob(db, job.JobID, fmt.Sprintf("Failed to create temp directory: %v", err))
		return
	}
	defer os.RemoveAll(tempDir)

	inputFilename := filepath.Base(job.InputS3Key)
	localInputPath := filepath.Join(tempDir, inputFilename)

	err = minioClient.FGetObject(ctx, cfg.S3Bucket, job.InputS3Key, localInputPath, minio.GetObjectOptions{})
	if err != nil {
		failJob(db, job.JobID, fmt.Sprintf("Failed to download input file from MinIO: %v", err))
		return
	}

	execCtx, cancelExec := context.WithTimeout(ctx, 3*time.Minute)
	defer cancelExec()

	var outputLocalPath string

	targetLower := strings.ToLower(job.TargetFormat)

	if strings.HasPrefix(targetLower, "compress") || targetLower == "zip" {
		outputLocalPath, err = compressFile(execCtx, tempDir, localInputPath, targetLower)
	} else if isOfficeDocument(inputFilename) {
		outputLocalPath, err = convertWithLibreOffice(execCtx, tempDir, localInputPath, targetLower)
	} else {
		outputLocalPath, err = convertWithImageMagick(execCtx, tempDir, localInputPath, targetLower)
	}

	if err != nil {
		failJob(db, job.JobID, fmt.Sprintf("Processing failed: %v", err))
		return
	}

	if job.OutputS3Key == "" {
		outExt := filepath.Ext(outputLocalPath)
		if outExt == "" {
			outExt = "." + targetLower
		}
		job.OutputS3Key = fmt.Sprintf("temp_outputs/%s/output%s", job.JobID, outExt)
	}

	contentType := getMimeType(job.TargetFormat)
	_, err = minioClient.FPutObject(ctx, cfg.S3Bucket, job.OutputS3Key, outputLocalPath, minio.PutObjectOptions{
		ContentType: contentType,
	})

	if err != nil {
		failJob(db, job.JobID, fmt.Sprintf("Failed to upload output file to MinIO: %v", err))
		return
	}

	updateJobStatusWithOutput(db, job.JobID, "done", "", job.OutputS3Key)
	log.Printf("[Worker Goroutine] Job ID %s COMPLETED SUCCESSFULLY -> Output uploaded to S3: %s\n", job.JobID, job.OutputS3Key)
}

func convertWithLibreOffice(ctx context.Context, tempDir, inputPath, targetFormat string) (string, error) {
	ext := strings.ToLower(filepath.Ext(inputPath))
	target := strings.ToLower(targetFormat)
	baseName := strings.TrimSuffix(filepath.Base(inputPath), filepath.Ext(inputPath))
	expectedOutputPath := filepath.Join(tempDir, fmt.Sprintf("%s.%s", baseName, targetFormat))

	buildCmd := func(useInfilter string, specifier string, srcFile string) *exec.Cmd {
		args := []string{
			"--headless",
			"--invisible",
			"--nologo",
			"--nolockcheck",
			"--nodefault",
			"--norestore",
		}
		if useInfilter != "" {
			args = append(args, fmt.Sprintf("--infilter=%s", useInfilter))
		}
		if specifier != "" {
			args = append(args, "--convert-to", specifier)
		} else {
			args = append(args, "--convert-to", target)
		}
		args = append(args, "--outdir", tempDir, srcFile)
		return exec.CommandContext(ctx, "soffice", args...)
	}

	// 1. SPECIAL HIGH-ACCURACY PIPELINE: PDF to Excel (.xlsx / .xls)
	if ext == ".pdf" && (target == "xlsx" || target == "xls") {
		log.Println("[Worker PDF2XLSX] Executing PDF -> HTML -> Calc XLSX Pipeline...")
		htmlPath := filepath.Join(tempDir, fmt.Sprintf("%s.html", baseName))
		
		cmdHTML := exec.CommandContext(ctx, "pdftohtml", "-s", "-i", "-noframes", inputPath, htmlPath)
		cmdHTML.Run()

		if _, statErr := os.Stat(htmlPath); statErr == nil {
			cmdCalc := buildCmd("HTML (StarCalc)", target, htmlPath)
			outputCalc, errCalc := cmdCalc.CombinedOutput()
			if errCalc == nil {
				if info, statErr := os.Stat(expectedOutputPath); statErr == nil && info.Size() > 0 {
					return expectedOutputPath, nil
				}
				files, _ := filepath.Glob(filepath.Join(tempDir, fmt.Sprintf("*.%s", targetFormat)))
				if len(files) > 0 {
					return files[0], nil
				}
			}
			log.Printf("[Worker PDF2XLSX] HTML (StarCalc) conversion note: %v (%s)\n", errCalc, string(outputCalc))
		}
	}

	// 2. Standard LibreOffice Conversion
	primaryCmd := buildCmd("", target, inputPath)
	output, err := primaryCmd.CombinedOutput()

	if err == nil {
		if info, statErr := os.Stat(expectedOutputPath); statErr == nil && info.Size() > 1000 {
			return expectedOutputPath, nil
		}
		files, _ := filepath.Glob(filepath.Join(tempDir, fmt.Sprintf("*.%s", targetFormat)))
		for _, f := range files {
			if info, statErr := os.Stat(f); statErr == nil && info.Size() > 1000 {
				return f, nil
			}
		}
	}

	// Fallback for PDF to DOCX using writer_pdf_import if direct conversion failed or produced dummy empty file
	if ext == ".pdf" && (target == "docx" || target == "doc") {
		fallbackCmd := buildCmd("writer_pdf_import", "docx", inputPath)
		outputFB, errFB := fallbackCmd.CombinedOutput()
		if errFB == nil {
			if info, statErr := os.Stat(expectedOutputPath); statErr == nil && info.Size() > 0 {
				return expectedOutputPath, nil
			}
			files, _ := filepath.Glob(filepath.Join(tempDir, fmt.Sprintf("*.%s", targetFormat)))
			if len(files) > 0 {
				return files[0], nil
			}
		}
		return "", fmt.Errorf("soffice exec error: %v (fallback: %v), output: %s %s", err, errFB, string(output), string(outputFB))
	}

	return "", fmt.Errorf("soffice exec error: %v, output: %s", err, string(output))
}

func compressFile(ctx context.Context, tempDir, inputPath, targetFormat string) (string, error) {
	ext := strings.ToLower(filepath.Ext(inputPath))
	baseName := strings.TrimSuffix(filepath.Base(inputPath), filepath.Ext(inputPath))
	
	outExt := ext
	if targetFormat == "zip" {
		outExt = ".zip"
	}
	outputPath := filepath.Join(tempDir, fmt.Sprintf("%s_compressed%s", baseName, outExt))

	pdfSettings := "/ebook"
	if strings.Contains(targetFormat, "max") || strings.Contains(targetFormat, "screen") {
		pdfSettings = "/screen"
	} else if strings.Contains(targetFormat, "mail") || strings.Contains(targetFormat, "printer") {
		pdfSettings = "/printer"
	}

	switch ext {
	case ".pdf":
		// Ghostscript PDF Compression & Resizing (72-150 DPI)
		cmd := exec.CommandContext(ctx, "gs",
			"-sDEVICE=pdfwrite",
			"-dCompatibilityLevel=1.4",
			fmt.Sprintf("-dPDFSETTINGS=%s", pdfSettings),
			"-dNOPAUSE",
			"-dQUIET",
			"-dBATCH",
			fmt.Sprintf("-sOutputFile=%s", outputPath),
			inputPath,
		)
		output, err := cmd.CombinedOutput()
		if err == nil {
			if info, statErr := os.Stat(outputPath); statErr == nil && info.Size() > 0 {
				log.Printf("[Compress PDF Success] Compressed PDF size: %d bytes (Input: %s)\n", info.Size(), inputPath)
				return outputPath, nil
			}
		}
		log.Printf("[Compress Fallback] Ghostscript error (%v: %s). Retrying ImageMagick PDF resize...\n", err, string(output))

		cmdIM := exec.CommandContext(ctx, "convert", "-density", "140", inputPath, "-quality", "60", "-compress", "jpeg", outputPath)
		outputIM, errIM := cmdIM.CombinedOutput()
		if errIM == nil {
			if info, statErr := os.Stat(outputPath); statErr == nil && info.Size() > 0 {
				return outputPath, nil
			}
		}
		return "", fmt.Errorf("PDF compression error: %v (%s)", errIM, string(outputIM))

	case ".jpg", ".jpeg", ".png", ".webp":
		cmd := exec.CommandContext(ctx, "convert", inputPath, "-resize", "1920x1920>", "-quality", "65", "-strip", outputPath)
		output, err := cmd.CombinedOutput()
		if err != nil {
			return "", fmt.Errorf("Image compression error: %v (%s)", err, string(output))
		}
		return outputPath, nil

	case ".docx", ".xlsx", ".pptx":
		err := recompressZip(inputPath, outputPath)
		if err != nil {
			return "", fmt.Errorf("Office document compression error: %v", err)
		}
		return outputPath, nil

	default:
		cmd := exec.CommandContext(ctx, "zip", "-9", "-j", outputPath, inputPath)
		output, err := cmd.CombinedOutput()
		if err == nil {
			return outputPath, nil
		}
		return "", fmt.Errorf("Compression error for file %s: %s", ext, string(output))
	}
}

func recompressZip(src, dst string) error {
	r, err := zip.OpenReader(src)
	if err != nil {
		return err
	}
	defer r.Close()

	out, err := os.Create(dst)
	if err != nil {
		return err
	}
	defer out.Close()

	w := zip.NewWriter(out)
	w.RegisterCompressor(zip.Deflate, func(out io.Writer) (io.WriteCloser, error) {
		return flate.NewWriter(out, flate.BestCompression)
	})
	defer w.Close()

	for _, f := range r.File {
		rc, err := f.Open()
		if err != nil {
			return err
		}

		header := f.FileHeader
		header.Method = zip.Deflate
		fw, err := w.CreateHeader(&header)
		if err != nil {
			rc.Close()
			return err
		}

		_, err = io.Copy(fw, rc)
		rc.Close()
		if err != nil {
			return err
		}
	}
	return nil
}

func convertWithImageMagick(ctx context.Context, tempDir, inputPath, targetFormat string) (string, error) {
	baseName := strings.TrimSuffix(filepath.Base(inputPath), filepath.Ext(inputPath))
	outputPath := filepath.Join(tempDir, fmt.Sprintf("%s.%s", baseName, targetFormat))

	cmd := exec.CommandContext(ctx, "convert", inputPath, outputPath)
	output, err := cmd.CombinedOutput()
	if err != nil {
		return "", fmt.Errorf("ImageMagick exec error: %v, output: %s", err, string(output))
	}

	return outputPath, nil
}

func updateJobStatus(db *sql.DB, jobID, status, errMsg string) {
	updateJobStatusWithOutput(db, jobID, status, errMsg, "")
}

func updateJobStatusWithOutput(db *sql.DB, jobID, status, errMsg, outputS3Key string) {
	if outputS3Key != "" {
		query := `UPDATE jobs SET status = $1, error_message = $2, output_s3_key = $3, updated_at = NOW() WHERE id = $4`
		_, err := db.Exec(query, status, errMsg, outputS3Key, jobID)
		if err != nil {
			log.Printf("[DB Error] Failed to update job %s to %s: %v\n", jobID, status, err)
		}
	} else {
		query := `UPDATE jobs SET status = $1, error_message = $2, updated_at = NOW() WHERE id = $3`
		_, err := db.Exec(query, status, errMsg, jobID)
		if err != nil {
			log.Printf("[DB Error] Failed to update job %s to %s: %v\n", jobID, status, err)
		}
	}
}

func failJob(db *sql.DB, jobID, errMsg string) {
	log.Printf("[Job Failed] Job ID: %s - Error: %s\n", jobID, errMsg)
	updateJobStatus(db, jobID, "failed", errMsg)
}

func isOfficeDocument(filename string) bool {
	ext := strings.ToLower(filepath.Ext(filename))
	switch ext {
	case ".pdf", ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".odt", ".rtf", ".txt", ".csv":
		return true
	default:
		return false
	}
}

func getMimeType(format string) string {
	switch strings.ToLower(format) {
	case "pdf":
		return "application/pdf"
	case "docx":
		return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
	case "xlsx":
		return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
	case "png":
		return "image/png"
	case "jpg", "jpeg":
		return "image/jpeg"
	default:
		return "application/octet-stream"
	}
}
