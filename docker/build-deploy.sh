#!/bin/bash
# Build image + deploy, dengan sentinel marker untuk monitoring.
# Pakai --no-cache supaya perubahan file (mis. migration baru) selalu kebawa.
# Jalankan di VPS: bash ~/brilink-h12/docker/build-deploy.sh
LOG=/tmp/bd.log
: > "$LOG"
echo "START $(date)" >> "$LOG"

cd ~/brilink-h12 || { echo "NO_DIR" >> "$LOG"; exit 1; }

if sudo docker build --no-cache -t brilink-h12 . >> "$LOG" 2>&1; then
    echo "BUILD_OK" >> "$LOG"
    if bash deploy.sh >> "$LOG" 2>&1; then
        echo "SENTINEL_DEPLOY_OK" >> "$LOG"
    else
        echo "SENTINEL_DEPLOY_FAIL" >> "$LOG"
    fi
else
    echo "SENTINEL_BUILD_FAIL" >> "$LOG"
fi
