#!/usr/bin/env sh

# PATH tối thiểu cho cron
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
export PATH

LOG_DIR="/var/log/apache2"
REPORT_DIR="/var/log/apache2_stats"
GOACCESS_BIN="/usr/bin/goaccess"

# đảm bảo thư mục tồn tại
[ -d "$REPORT_DIR" ] || mkdir -p "$REPORT_DIR"

gen_log() {
    src="$1"
    out="$2"

    "$GOACCESS_BIN" "$src" \
        -o "$out" \
        --no-global-config \
        --time-format='%H:%M:%S' \
        --date-format='%d/%b/%Y' \
        --log-format='COMBINED' \
        --ignore-panel='KEYPHRASES' \
        --ignore-panel='VISIT_TIMES' \
        --html-prefs='{"autoHideTables":false,"layout":"horizontal","perPage":100,"theme":"bright","hiddenPanels":[],"showTables":true,"visitors":{"plot":{"chartType":"bar","metric":"hits-visitors"},"chart":false,"columns":{"bytes":{"hide":true}}},"requests":{"chart":false,"columns":{"protocol":{"hide":true},"visitors":{"hide":true},"bytes":{"hide":true}}},"static_requests":{"chart":false,"columns":{"visitors":{"hide":true},"protocol":{"hide":true},"bytes":{"hide":true}}},"not_found":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true},"protocol":{"hide":true}}},"hosts":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true}}},"os":{"chart":false,"columns":{"bytes":{"hide":true},"hits":{"hide":true}}},"browsers":{"chart":false,"columns":{"hits":{"hide":true},"bytes":{"hide":true}}},"referrers":{"chart":false,"columns":{"bytes":{"hide":true},"visitors":{"hide":true}}},"referring_sites":{"chart":false,"columns":{"bytes":{"hide":true},"visitors":{"hide":true}}},"status_codes":{"chart":false,"columns":{"visitors":{"hide":true},"bytes":{"hide":true}}}}'
}

# duyệt file log (an toàn với cron)
for LOG_FILE in "$LOG_DIR"/*.access.log.1; do
    [ -f "$LOG_FILE" ] || continue

    FILENAME=$(basename "$LOG_FILE")
    DOMAIN=${FILENAME%.access.log}
    REPORT_FILE="$REPORT_DIR/$DOMAIN.html"

    gen_log "$LOG_FILE" "$REPORT_FILE"
done

exit 0
