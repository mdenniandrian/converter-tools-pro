# Stage 1: Build Go Binary
FROM golang:1.22-alpine AS builder

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache git

# Copy all source files first so go mod tidy can inspect imports in main.go
COPY . .

# Auto-generate go.sum based on actual imports in main.go
RUN go mod tidy

# Build binary
RUN CGO_ENABLED=0 GOOS=linux go build -ldflags="-w -s" -o worker .

# Stage 2: Runtime Container with LibreOffice, ImageMagick, Ghostscript, JRE & Poppler Utils
FROM alpine:3.19

RUN apk add --no-cache \
    libreoffice \
    imagemagick \
    ghostscript \
    poppler-utils \
    openjdk11-jre-headless \
    zip \
    ttf-dejavu \
    font-noto \
    ca-certificates \
    tzdata

WORKDIR /app

COPY --from=builder /app/worker /app/worker

ENTRYPOINT ["/app/worker"]
