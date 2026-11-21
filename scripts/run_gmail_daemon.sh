#!/bin/bash
# Starts the continuous Gmail Payment Listener in the background with auto-restart
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOG="$DIR/storage/logs/gmail_daemon.log"
PID_FILE="$DIR/storage/logs/gmail_daemon.pid"

mkdir -p "$DIR/storage/logs"

# Check if already running
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if kill -0 "$PID" 2>/dev/null; then
        echo "Gmail listener is already running (PID $PID)."
        exit 0
    fi
fi

echo "Starting continuous 24/7 Gmail listener..."
nohup python3 -u "$DIR/scripts/gmail_direct_listener.py" "scrcgsfhyiydpfmh" "rootlinux09@gmail.com" >> "$LOG" 2>&1 &
echo $! > "$PID_FILE"
echo "Gmail listener started in background (PID $(cat "$PID_FILE"))."
echo "Log: $LOG"
