# this requires sudo dnf install inotify-tools wl-clipboard
while inotifywait -e close_write ../core/cache/speedscope.json; do
    wl-copy < ../core/cache/speedscope.json
    echo "Copied updated file to clipboard at $(date)"
done
