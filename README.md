# ⚡ Convertify Pro - All-in-One File Converter, Compressor & Enterprise Backoffice

![License](https://img.shields.io/badge/License-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4.svg)
![Golang](https://img.shields.io/badge/Golang-1.22-00ADD8.svg)
![Python](https://img.shields.io/badge/Python-3.10-3776AB.svg)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1.svg)
![Redis](https://img.shields.io/badge/Redis-7.0-DC382D.svg)

**Convertify Pro** is a state-of-the-art, enterprise-grade document conversion, file compression, and AI background removal platform. Built with a high-performance microservices architecture (Laravel PHP Gateway, Golang Worker, Python AI Worker, PostgreSQL, Redis, and MinIO S3 Object Storage).

---

## 🏗️ Microservices Architecture

```
                       ┌─────────────────────────┐
                       │   Client Web Browser    │
                       └────────────┬────────────┘
                                    │ HTTP / REST API
                                    ▼
                       ┌─────────────────────────┐
                       │   Laravel PHP Gateway   │
                       │     (Port: 8000)        │
                       └─────┬──────────────┬────┘
                             │              │
        ┌────────────────────┴──┐        ┌──┴────────────────────┐
        │ PostgreSQL Database   │        │   MinIO S3 Storage    │
        │      (Port: 5432)     │        │  (Port: 9000 / 9001)  │
        └───────────────────────┘        └───────────────────────┘
                                    ▲
                                    │ Redis Job Queues
                             ┌──────┴──────┐
                             │   Redis 7   │
                             │ (Port: 6379)│
                             └──────┬──────┘
                                    │
            ┌───────────────────────┴───────────────────────┐
            ▼                                               ▼
┌─────────────────────────┐                     ┌─────────────────────────┐
│     Golang Worker       │                     │    Python AI Worker     │
│  (LibreOffice, Poppler, │                     │  (rembg U2-Net Model,   │
│   ImageMagick, GS, Zip) │                     │   Background Removal)   │
└─────────────────────────┘                     └─────────────────────────┘
```

---

## ✨ Key Features & Capability Matrix

### 🔀 File Conversion & Processing
- 📄 **PDF to Word (.docx):** Precise layout preservation using `writer_pdf_import`.
- 📊 **PDF to Excel (.xlsx):** High-accuracy table extraction via multi-step PDF-to-HTML StarCalc conversion pipeline.
- 📝 **Word / Excel to PDF:** Lossless document compilation to PDF format.
- 🖼️ **Multi-Format Image Converter:** Convert between JPG, PNG, WEBP, and PDF.
- 🗜️ **File Compression:** 
  - **PDF Compression:** 3 compression presets (Standard Mail `-dPDFSETTINGS=/printer`, Medium eBook `-dPDFSETTINGS=/ebook`, and Max Screen `-dPDFSETTINGS=/screen`) powered by Ghostscript engine (`gs`).
  - **Word / Excel Compression:** Deep ZIP deflate re-compression.
  - **Image Compression:** Dynamic ImageMagick quality reduction.
- ✨ **AI Background Remover (remove.bg Style):** Powered by Python `rembg` U2-Net model with real-time transparent preview.

### 🔒 Enterprise Security & Clean Storage
- 🗑️ **Auto Stream-and-Delete:** Temporary upload files and converted output files are automatically purged from MinIO S3 immediately after being streamed to the user.
- 🛡️ **Credential Protection:** Credentials, API keys, and environment configs strictly isolated via `.env` and `.gitignore`.

### 🏢 Enterprise Backoffice Admin Panel (`/admin`)
- 📊 **Analytics Dashboard:** Live metrics for total processed jobs, registered users, and activation codes.
- ⚙️ **Dynamic System Settings:** Live toggles for Midtrans Payment Gateway, WhatsApp Admin Direct Order, and Sandbox Simulator.
- 💰 **Pricing & Discount Control:** Set custom PRO & Enterprise plan pricing and active coupon discount codes.
- 🔑 **Activation Code Generator:** Bulk serial key generator (1-50 keys) with expiration tracking.
- 👥 **User Management:** Instant plan elevation (`FREE`, `PRO`, `ENTERPRISE`), role changes (`ADMIN`, `USER`), and account deletion.
- 🤖 **Telegram Bot Integration:** Automatic real-time alerts sent to your Telegram chat for user registrations, plan upgrades, and system events.

---

## 🚀 Quick Start Guide (Docker Compose - RECOMMENDED)

Docker Compose is the fastest and most reliable way to run Convertify Pro on both **Linux** and **Windows**.

### Step 1: Clone Repository
```bash
git clone https://github.com/mdenniandrian/converter-tools.git
cd converter-tools
```

### Step 2: Configure Environment (`.env`)
Copy the environment template:
```bash
cp .env.example .env
```

Review and adjust environment variables in `.env`:
```env
# Database PostgreSQL Configuration
DB_DATABASE=converter_db
DB_USERNAME=converter_user
DB_PASSWORD=secret123

# MinIO / S3 Configuration
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin123
AWS_BUCKET=temp-converter-files

# WhatsApp Admin Configuration
WA_ADMIN_NUMBER=6282113237920

# Midtrans Payment Gateway Configuration
MIDTRANS_SERVER_KEY=Mid-server-YOUR_SERVER_KEY
MIDTRANS_CLIENT_KEY=Mid-client-YOUR_CLIENT_KEY
MIDTRANS_IS_PRODUCTION=false

# Telegram Bot Notifications
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
ENABLE_TELEGRAM_NOTIF=true
```

### Step 3: Build & Launch Services
```bash
docker compose up -d --build
```

### Step 4: Access Application & Backoffice
- **Main Web Application:** `http://localhost:8000`
- **Default Admin Login:** `admin@convertify.local`
- **Default Password:** `admin123`

---

## 🐧 Linux OS Setup Guide (Ubuntu / Debian VPS)

### Option A: Docker Deployment (Recommended for VPS)

1. **Install Docker Engine & Docker Compose Plugin:**
   ```bash
   sudo apt update
   sudo apt install -y curl git docker.io docker-compose-plugin
   sudo systemctl enable --now docker
   sudo usermod -aG docker $USER
   ```

2. **Clone & Launch Application:**
   ```bash
   git clone https://github.com/mdenniandrian/converter-tools.git
   cd converter-tools
   cp .env.example .env
   docker compose up -d --build
   ```

3. **Configure Nginx Reverse Proxy with SSL (Optional for Production Domain):**
   ```bash
   sudo apt install -y nginx certbot python3-certbot-nginx
   ```
   Create `/etc/nginx/sites-available/converter.conf`:
   ```nginx
   server {
       server_name yourdomain.com;

       location / {
           proxy_pass http://127.0.0.1:8000;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
           client_max_body_size 100M;
       }
   }
   ```
   Enable site and obtain SSL certificate:
   ```bash
   sudo ln -s /etc/nginx/sites-available/converter.conf /etc/nginx/sites-enabled/
   sudo nginx -t && sudo systemctl reload nginx
   sudo certbot --nginx -d yourdomain.com
   ```

---

### Option B: Native Bare-Metal Setup (Linux without Docker)

If running directly on Ubuntu 22.04 / 24.04 server without Docker:

1. **Install Dependencies:**
   ```bash
   sudo apt update && sudo apt install -y \
     php8.2-fpm php8.2-pgsql php8.2-zip php8.2-bcmath php8.2-intl php8.2-curl \
     postgresql postgresql-contrib redis-server \
     libreoffice imagemagick ghostscript poppler-utils openjdk-11-jre-headless zip \
     python3 python3-pip python3-venv golang git
   ```

2. **Configure PostgreSQL Database:**
   ```bash
   sudo -u postgres psql -c "CREATE USER converter_user WITH PASSWORD 'secret123';"
   sudo -u postgres psql -c "CREATE DATABASE converter_db OWNER converter_user;"
   ```

3. **Install & Run MinIO S3 Server:**
   ```bash
   wget https://dl.min.io/server/minio/release/linux-amd64/minio
   chmod +x minio
   MINIO_ROOT_USER=minioadmin MINIO_ROOT_PASSWORD=minioadmin123 ./minio server /data --console-address ":9001" &
   ```

4. **Run Python AI Background Remover Worker:**
   ```bash
   cd worker-python
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   python3 main.py &
   ```

5. **Run Golang Document Worker:**
   ```bash
   cd worker-golang
   go build -o worker main.go
   ./worker &
   ```

6. **Serve Laravel Gateway:**
   ```bash
   cd laravel-app
   php -S 0.0.0.0:8000 -t public
   ```

---

## 🪟 Windows OS Setup Guide

### Option A: Using Docker Desktop (Recommended for Windows)

1. Download and install [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/).
2. Enable **WSL 2 (Windows Subsystem for Linux)** during installation.
3. Open **PowerShell** or **Git Bash**:
   ```powershell
   git clone https://github.com/mdenniandrian/converter-tools.git
   cd converter-tools
   Copy-Item .env.example .env
   docker compose up -d --build
   ```
4. Open your web browser at `http://localhost:8000`.

---

### Option B: Local Windows Setup without Docker

1. **Install Prerequisites:**
   - **PHP 8.2 for Windows:** Download from [windows.php.net](https://windows.php.net/download/) and add to System PATH.
   - **PostgreSQL for Windows:** Install via EnterpriseDB installer.
   - **Memurai or Redis for Windows:** Install Redis 7 service.
   - **LibreOffice for Windows:** Install LibreOffice (`soffice.exe`).
   - **Python 3.10+:** Install Python and add to System PATH.
   - **Golang 1.22+:** Install Go for Windows.

2. **Configure Environment:**
   In PowerShell, copy `.env.example` to `.env` and configure DB / MinIO credentials.

3. **Start Workers in Separate Terminals:**
   - **Python Worker:**
     ```powershell
     cd worker-python
     pip install -r requirements.txt
     python main.py
     ```
   - **Golang Worker:**
     ```powershell
     cd worker-golang
     go run main.go
     ```
   - **PHP Development Server:**
     ```powershell
     cd laravel-app
     php -S localhost:8000 -t public
     ```

---

## ⚙️ Environment Variables Reference

| Variable Name | Description | Default Value |
| :--- | :--- | :--- |
| `DB_DATABASE` | PostgreSQL Database Name | `converter_db` |
| `DB_USERNAME` | PostgreSQL User | `converter_user` |
| `DB_PASSWORD` | PostgreSQL Password | `secret123` |
| `MINIO_ROOT_USER` | MinIO Access Key ID | `minioadmin` |
| `MINIO_ROOT_PASSWORD` | MinIO Secret Access Key | `minioadmin123` |
| `AWS_BUCKET` | MinIO Bucket Name | `temp-converter-files` |
| `WA_ADMIN_NUMBER` | WhatsApp Admin Phone (International format, e.g. `628...`) | `6282113237920` |
| `MIDTRANS_SERVER_KEY` | Midtrans Server Key | `Mid-server-...` |
| `MIDTRANS_CLIENT_KEY` | Midtrans Client Key | `Mid-client-...` |
| `MIDTRANS_IS_PRODUCTION` | Midtrans Production Mode (`true` / `false`) | `false` |
| `TELEGRAM_BOT_TOKEN` | BotFather Telegram API Token | `123456789:ABC...` |
| `TELEGRAM_CHAT_ID` | Telegram Chat ID for Admin Alerts | `987654321` |
| `ENABLE_TELEGRAM_NOTIF` | Enable Telegram Notifications (`true` / `false`) | `true` |

---

## 🛡️ Production & Security Checklist

1. **Change Default Passwords:** Change default PostgreSQL, MinIO, and Super Admin passwords in `.env` before public deployment.
2. **Git Credential Protection:** Ensure `.env` is listed in `.gitignore` so secrets are never pushed to public repositories.
3. **Firewall Rules:** Expose only port `80` / `443` publicly. Keep ports `5432` (DB), `6379` (Redis), and `9000/9001` (MinIO) restricted to internal container network.

---

## 📜 License

Distributed under the MIT License. See `LICENSE` for details.
