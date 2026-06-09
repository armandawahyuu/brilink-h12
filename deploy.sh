#!/bin/bash
# Deploy script - jalankan di VPS
set -e

cd ~/brilink-h12

mkdir -p ~/brilink-backups

if sudo docker ps --format '{{.Names}}' | grep -qx brilink; then
  sudo docker cp brilink:/data/database.sqlite ~/brilink-backups/database-$(date +%Y%m%d%H%M%S).sqlite 2>/dev/null || true
fi

# Stop & remove existing container
sudo docker rm -f brilink 2>/dev/null || true

# Create volume for persistent DB
sudo docker volume create brilink-db 2>/dev/null || true

# Run container (volume di-mount ke /data, terpisah dari folder migrations)
sudo docker run -d \
  --name brilink \
  --restart unless-stopped \
  --env-file ~/brilink-h12/.env \
  -p 127.0.0.1:8080:8080 \
  -v brilink-db:/data \
  brilink-h12:latest

echo "=== Container Status ==="
sleep 2
sudo docker ps --filter name=brilink
echo ""
echo "=== Logs ==="
sudo docker logs brilink 2>&1 | tail -10
echo ""
echo "DEPLOY DONE - access at https://bukuagen.my.id"
