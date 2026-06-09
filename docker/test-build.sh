#!/bin/bash
LOG=/tmp/tb.log
: > "$LOG"
echo "START $(date)" >> "$LOG"
cd ~/brilink-h12 || { echo "NO_DIR" >> "$LOG"; exit 1; }

echo "=CONTEXT_CHECK=" >> "$LOG"
ls database/migrations/ | grep payment >> "$LOG" 2>&1

if sudo docker build --no-cache -t brilink-test . >> "$LOG" 2>&1; then
    echo "BUILD_OK" >> "$LOG"
    echo "=IMAGE_MIGRATIONS=" >> "$LOG"
    sudo docker run --rm brilink-test ls database/migrations/ >> "$LOG" 2>&1
    echo "SENTINEL_TEST_DONE" >> "$LOG"
else
    echo "SENTINEL_TEST_BUILD_FAIL" >> "$LOG"
fi
