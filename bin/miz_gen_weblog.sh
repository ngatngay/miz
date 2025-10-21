#!/bin/sh

LOG_DIR="/www/log/apache"
REPORT_DIR="/www/log/apache_html"

mkdir -p "$REPORT_DIR"
#rm -rf "$REPORT_DIR"/*

gen_log() {
    src=$1
    out=$2

    cat "$src" | goaccess \
        -o "$out" \
        --no-global-config \
        --time-format='%H:%M:%S' \
        --date-format='%d/%b/%Y' \
        --log-format='COMBINED' \
        --ignore-panel='KEYPHRASES' \
        --ignore-panel='VISIT_TIMES' \
        --html-prefs='{"autoHideTables":false,"layout":"horizontal","perPage":100,"theme":"bright","hiddenPanels":[],"showTables":true,"visitors":{"plot":{"chartType":"bar","metric":"hits-visitors"},"chart":false,"columns":{"bytes":{"hide":true}}},"requests":{"chart":false,"columns":{"protocol":{"hide":true},"visitors":{"hide":true},"bytes":{"hide":true}}},"static_requests":{"chart":false,"columns":{"visitors":{"hide":true},"protocol":{"hide":true},"bytes":{"hide":true}}},"not_found":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true},"protocol":{"hide":true}}},"hosts":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true}}},"os":{"chart":false,"columns":{"bytes":{"hide":true},"hits":{"hide":true}}},"browsers":{"chart":false,"columns":{"hits":{"hide":true},"bytes":{"hide":true}}},"referrers":{"chart":false,"columns":{"bytes":{"hide":true},"visitors":{"hide":true}}},"referring_sites":{"chart":false,"columns":{"bytes":{"hide":true},"visitors":{"hide":true}}},"status_codes":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true}}}}'
}

find "$LOG_DIR" -type f -name "*.access.log" | while IFS= read LOG_FILE; do
    FILENAME=$(basename "$LOG_FILE")
    DOMAIN=$(echo "$FILENAME" | sed 's/\.access\.log$//')
    REPORT_FILE="$REPORT_DIR/$DOMAIN"

    echo "$FILENAME"
    gen_log "$LOG_FILE" "$REPORT_FILE.html"
    if [ -f "$LOG_FILE.1" ]; then
        gen_log "$LOG_FILE.1" "$REPORT_FILE.prev.html"
    fi
done

chown -R www-data:www-data /www/log
