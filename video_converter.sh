#!/bin/bash

# Директории
RAW_DIR="/var/www/html/raw"
VIDEO_DIR="/var/www/html/video"
LOG_FILE="/var/www/html/video_converter.log"   # опционально

# Debug mode (0 = off, 1 = on)
DEBUG=${DEBUG:-0}
DEBUG_LOG="/var/www/html/ffmpeg_debug.log"

SKIP_CONVERSION_EXTENSIONS=("mp4" "m4v" "mov" "webm" "ogg" "ogv")

mkdir -p "$RAW_DIR" "$VIDEO_DIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

debug_log() {
    if [ "$DEBUG" -eq 1 ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$DEBUG_LOG"
    fi
}

set_ownership() {
    local path="$1"
    if chown www-data:www-data "$path" 2>/dev/null; then
        log "INFO: ownership set to www-data:www-data for $path"
    else
        log "WARNING: failed to set ownership for $path (maybe not running as root?)"
    fi
}

should_skip_conversion() {
    local ext="$1"
    for skip_ext in "${SKIP_CONVERSION_EXTENSIONS[@]}"; do
        if [[ "$ext" == "$skip_ext" ]]; then
            return 0
        fi
    done
    return 1
}

wait_for_file() {
    local file="$1"
    local max_wait=30
    local wait_interval=2
    local elapsed=0

    [ -f "$file" ] || return 1

    if command -v lsof >/dev/null 2>&1; then
        while lsof "$file" >/dev/null 2>&1; do
            if [ $elapsed -ge $max_wait ]; then
                log "ERROR: timeout waiting for file write to finish: $file"
                return 1
            fi
            sleep "$wait_interval"
            elapsed=$((elapsed + wait_interval))
        done
    else
        local size1=0 size2=0
        size1=$(stat -c %s "$file" 2>/dev/null || echo "0")
        sleep 2
        size2=$(stat -c %s "$file" 2>/dev/null || echo "0")
        while [ "$size1" != "$size2" ]; do
            if [ $elapsed -ge $max_wait ]; then
                log "ERROR: timeout – file size still changing: $file"
                return 1
            fi
            size1=$size2
            sleep "$wait_interval"
            elapsed=$((elapsed + wait_interval))
            size2=$(stat -c %s "$file" 2>/dev/null || echo "0")
        done
    fi

    sleep 1
    return 0
}

# Get video info using ffprobe
get_video_info() {
    local input_file="$1"
    if command -v ffprobe >/dev/null 2>&1; then
        ffprobe -v error -show_format -show_streams "$input_file" 2>/dev/null
    else
        echo "ffprobe not available"
    fi
}

convert_to_mp4() {
    local input_file="$1"
    local output_file="$2"
    local tmp_output="${output_file}.tmp"

    mkdir -p "$(dirname "$output_file")"
    set_ownership "$(dirname "$output_file")"

    if [ "$DEBUG" -eq 1 ]; then
        debug_log "=== Starting conversion ==="
        debug_log "Input: $input_file"
        debug_log "Output: $output_file"
        debug_log "CPU cores available: $(nproc)"
        
        debug_log "Video info from ffprobe:"
        get_video_info "$input_file" | while IFS= read -r line; do
            debug_log "  $line"
        done
        
        # Log ffmpeg command
        debug_log "FFmpeg command: ffmpeg -y -nostdin -threads 0 -i \"$input_file\" -c:v libx264 -c:a aac -movflags +faststart -crf 23 -preset medium -threads 0 -f mp4 \"$tmp_output\""
        
        start_time=$(date +%s)
        debug_log "Start time: $(date)"
    fi

    # Run ffmpeg with -nostdin to prevent waiting for input
    # Use separate stdout/stderr redirection to avoid blocking
    if [ "$DEBUG" -eq 1 ]; then
        # In debug mode, capture all output to debug log
        ffmpeg -y -nostdin -threads 0 -i "$input_file" \
               -c:v libx264 -c:a aac \
               -movflags +faststart \
               -crf 23 -preset medium \
               -threads 0 -f mp4 "$tmp_output" >> "$DEBUG_LOG" 2>&1
        ffmpeg_exit=$?
    else
        # In normal mode, discard output but use a subshell to avoid blocking
        ( ffmpeg -y -nostdin -threads 0 -i "$input_file" \
                  -c:v libx264 -c:a aac \
                  -movflags +faststart \
                  -crf 23 -preset medium \
                  -threads 0 -f mp4 "$tmp_output" > /dev/null 2>&1 )
        ffmpeg_exit=$?
    fi

    if [ "$DEBUG" -eq 1 ]; then
        end_time=$(date +%s)
        elapsed=$((end_time - start_time))
        debug_log "End time: $(date)"
        debug_log "Elapsed time: ${elapsed} seconds"
        
        if command -v ffprobe >/dev/null 2>&1; then
            duration=$(ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "$input_file" 2>/dev/null)
            if [ -n "$duration" ] && [ "$elapsed" -gt 0 ]; then
                speed=$(echo "scale=2; $duration / $elapsed" | bc)
                debug_log "Video duration: ${duration} seconds"
                debug_log "Conversion speed: ${speed}x real-time"
            fi
        fi
        
        if command -v top >/dev/null 2>&1; then
            cpu_load=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
            debug_log "CPU load at end: ${cpu_load}%"
        fi
        
        debug_log "FFmpeg exit code: $ffmpeg_exit"
        debug_log "=== Conversion finished ===\n"
    fi

    if [ $ffmpeg_exit -eq 0 ] && [ -f "$tmp_output" ]; then
        mv "$tmp_output" "$output_file"
        set_ownership "$output_file"
        log "SUCCESS: converted $input_file -> $output_file"
        return 0
    else
        log "ERROR: failed to convert $input_file (exit code $ffmpeg_exit)"
        rm -f "$tmp_output"
        return 1
    fi
}

clean_empty_dirs() {
    find "$RAW_DIR" -type d -mindepth 1 ! -path "$RAW_DIR/error" ! -path "$RAW_DIR/error/*" -empty -print0 | while IFS= read -r -d '' dir; do
        if rmdir "$dir" 2>/dev/null; then
            log "INFO: removed empty directory $dir"
        fi
    done
}

# Main loop
log "Watcher started. Monitoring $RAW_DIR (recursively)"
log "Skipping conversion for: ${SKIP_CONVERSION_EXTENSIONS[*]}"
if [ "$DEBUG" -eq 1 ]; then
    log "DEBUG mode is ENABLED. Detailed logs will be written to $DEBUG_LOG"
else
    log "DEBUG mode is disabled. Set DEBUG=1 to enable detailed logging."
fi

while true; do
    find "$RAW_DIR" -type f ! -name "*.processing" ! -name "*.tmp" ! -name "*.part" -print0 | while IFS= read -r -d '' file; do

        relative_path="${file#$RAW_DIR/}"
        target_dir="$VIDEO_DIR/$(dirname "$relative_path")"
        base_name=$(basename "$file")

        lock_file="${file}.processing"
        [ -f "$lock_file" ] && continue
        touch "$lock_file"

        if ! wait_for_file "$file"; then
            rm -f "$lock_file"
            continue
        fi

        ext=$(echo "$base_name" | sed 's/.*\.//' | tr '[:upper:]' '[:lower:]')
        name_no_ext="${base_name%.*}"

        if should_skip_conversion "$ext"; then
            target_file="$target_dir/$base_name"
            mkdir -p "$target_dir"
            set_ownership "$target_dir"
            if mv "$file" "$target_file" 2>/dev/null; then
                set_ownership "$target_file"
                log "SUCCESS: moved $relative_path (no conversion needed)"
            else
                log "ERROR: failed to move $relative_path"
            fi
        else
            target_file="$target_dir/${name_no_ext}.mp4"
            if [ -f "$target_file" ]; then
                target_file="$target_dir/${name_no_ext}_$(date +%s).mp4"
                log "WARNING: target file already exists, saved as $(basename "$target_file")"
            fi

            if convert_to_mp4 "$file" "$target_file"; then
                rm -f "$file"
            else
                error_dir="$RAW_DIR/error/$(dirname "$relative_path")"
                mkdir -p "$error_dir"
                mv "$file" "$error_dir/" 2>/dev/null
                log "ERROR: source file moved to $error_dir"
            fi
        fi

        rm -f "$lock_file"
    done

    clean_empty_dirs
    sleep 5
done