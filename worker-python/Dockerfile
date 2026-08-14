FROM python:3.10-slim

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libgl1 \
    libglib2.0-0 \
    libgomp1 \
    curl \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Pre-download u2net model for rembg via curl to prevent RAM OOM spike during build
RUN mkdir -p /root/.u2net && \
    curl -fsSL https://github.com/danielgatis/rembg/releases/download/v0.0.0/u2net.onnx -o /root/.u2net/u2net.onnx

COPY . .

CMD ["python", "main.py"]
