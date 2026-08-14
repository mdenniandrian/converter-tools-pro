import os
import json
import time
import signal
import sys
import tempfile
import logging
import psycopg2
import redis
from minio import Minio
from rembg import remove
from PIL import Image

logging.basicConfig(level=logging.INFO, format='[Python Worker] %(asctime)s - %(levelname)s - %(message)s')

REDIS_HOST = os.getenv("REDIS_HOST", "localhost")
REDIS_PORT = int(os.getenv("REDIS_PORT", 6379))
REDIS_QUEUE = os.getenv("REDIS_QUEUE_KEY", "converter_jobs_bg")

DB_HOST = os.getenv("DB_HOST", "localhost")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_USER = os.getenv("DB_USER", "converter_user")
DB_PASSWORD = os.getenv("DB_PASSWORD", "secret123")
DB_NAME = os.getenv("DB_NAME", "converter_db")

S3_ENDPOINT = os.getenv("S3_ENDPOINT", "localhost:9000")
S3_ACCESS_KEY = os.getenv("S3_ACCESS_KEY", "minioadmin")
S3_SECRET_KEY = os.getenv("S3_SECRET_KEY", "minioadmin123")
S3_BUCKET = os.getenv("S3_BUCKET", "temp-converter-files")
S3_USE_SSL = os.getenv("S3_USE_SSL", "false").lower() == "true"

def get_db_connection():
    return psycopg2.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASSWORD,
        dbname=DB_NAME
    )

def update_job_status(job_id, status, error_message="", output_s3_key=None):
    try:
        conn = get_db_connection()
        cur = conn.cursor()
        if output_s3_key:
            cur.execute(
                "UPDATE jobs SET status = %s, error_message = %s, output_s3_key = %s, updated_at = NOW() WHERE id = %s",
                (status, error_message, output_s3_key, job_id)
            )
        else:
            cur.execute(
                "UPDATE jobs SET status = %s, error_message = %s, updated_at = NOW() WHERE id = %s",
                (status, error_message, job_id)
            )
        conn.commit()
        cur.close()
        conn.close()
    except Exception as e:
        logging.error(f"Database update failed for job {job_id}: {e}")

def process_bg_removal(minio_client, job):
    job_id = job.get("job_id")
    input_key = job.get("input_s3_key")
    output_key = job.get("output_s3_key") or f"temp_outputs/{job_id}/output.png"

    logging.info(f"Processing Background Removal for Job ID: {job_id}")
    update_job_status(job_id, "processing")

    with tempfile.TemporaryDirectory() as temp_dir:
        local_input = os.path.join(temp_dir, "input.img")
        local_output = os.path.join(temp_dir, "output.png")

        try:
            # 1. Download file from MinIO
            minio_client.fget_object(S3_BUCKET, input_key, local_input)

            # 2. Process image with rembg
            with open(local_input, "rb") as inp_f:
                input_bytes = inp_f.read()
                output_bytes = remove(input_bytes)

            with open(local_output, "wb") as out_f:
                out_f.write(output_bytes)

            # 3. Upload result to MinIO
            minio_client.fput_object(
                S3_BUCKET,
                output_key,
                local_output,
                content_type="image/png"
            )

            # 4. Mark job as done
            update_job_status(job_id, "done", "", output_key)
            logging.info(f"Job ID {job_id} successfully completed (Background Removed) -> S3: {output_key}")

        except Exception as e:
            logging.error(f"Error processing background removal for job {job_id}: {e}")
            update_job_status(job_id, "failed", str(e))

def main():
    logging.info("Starting Python Background Remover Worker (rembg)...")

    # Connect to Redis
    r = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, db=0)

    # Connect to MinIO
    minio_client = Minio(
        S3_ENDPOINT,
        access_key=S3_ACCESS_KEY,
        secret_key=S3_SECRET_KEY,
        secure=S3_USE_SSL
    )

    running = True

    def signal_handler(sig, frame):
        nonlocal running
        logging.info("Shutting down Python worker...")
        running = False

    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)

    while running:
        try:
            # BLPOP with 3s timeout
            item = r.blpop(REDIS_QUEUE, timeout=3)
            if item is None:
                continue

            _, payload_raw = item
            job = json.loads(payload_raw.decode('utf-8'))
            process_bg_removal(minio_client, job)

        except Exception as e:
            logging.error(f"Worker main loop exception: {e}")
            time.sleep(1)

if __name__ == "__main__":
    main()
