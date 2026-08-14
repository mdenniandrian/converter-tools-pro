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

# Pre-download u2net model for rembg so container starts instantly without waiting for network download
RUN python -c "from rembg import new_session; new_session('u2net')"

COPY . .

CMD ["python", "main.py"]
