<?php

define('ACCESS', true);
require __DIR__ . '/_init.php';

$site_title = 'cmd';
$command = trim((string) ($_GET['cmd'] ?? ''));

if (isset($_GET['stream']) && !empty($command)) {
    ini_set('zlib.output_compression', '0');
    ini_set('output_buffering', 'Off');
    set_time_limit(0);

    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-Encoding: identity');
    header('X-Accel-Buffering: no');

    ob_implicit_flush(true);
    while (ob_get_level() > 0) {
        ob_end_flush();
    }

  //

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
    ];
    $process = proc_open($command . ' 2>&1', $descriptors, $pipes);

    if (is_resource($process)) {
        fclose($pipes[0]);
        $exit_code = -1;

        while ($line = fgets($pipes[1])) {
            echo 'data: ' . json_encode($line) . "\n\n";
            echo ':' . str_repeat(' ', 10000) . "\n";
            flush();
        }

        fclose($pipes[1]);
        $exit_code = proc_close($process);
    } else {
        $exit_code = -1;
    }

    echo "event: done\ndata: $exit_code\n\n";
    flush();
    exit;
}

require SITE_HEADER;

?>

<h1 class="h4 mb-3">Run command</h1>

<div class="cmd-form mb-3">
    <div class="mb-2">
        <label class="form-label" for="command">Command</label>
        <textarea class="form-control font-monospace" id="command" name="command" rows="5"><?= e($command) ?></textarea>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="run-btn">Chạy lệnh</button>
    </div>
</div>

<div id="run-loading" class="text-body-secondary mb-2" style="display:none;">
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    <small>Đang chạy lệnh...</small>
</div>
<div id="run-result">
        <pre id="run-result-output" class="code-custom"></pre>
</div>

<script>
(() => {
    const run_btn = document.getElementById("run-btn");
    const loading_el = document.getElementById("run-loading");
    const result_wrap = document.getElementById("run-result");
    const result_output = document.getElementById("run-result-output");
    let es = null;

    if (!run_btn || !loading_el || !result_wrap || !result_output) {
        return;
    }

    const stop = () => {
        if (es) {
            es.close();
            es = null;
        }
    };

    const render_result = (ok) => {
        result_wrap.style.display = "";
        result_output.style.borderColor = ok ? "#198754" : "#dc3545";
        result_output.style.color = ok ? "#198754" : "#dc3545";
    };

    const run = () => {
        if (run_btn.disabled) {
            alert("Lệnh đang được chạy");
            return;
        }

        const cmd = document.getElementById("command").value;
        if (cmd.trim() === "") {
            alert("Vui lòng nhập lệnh");
            return;
        }

        loading_el.style.display = "";
        run_btn.disabled = true;
        result_output.textContent = "";
        result_output.style.borderColor = "#0d6efd";
        result_output.style.color = "#e2e8f0";

        es = new EventSource("/cmd.php?cmd=" + encodeURIComponent(cmd) + "&stream=1");

        es.onmessage = (e) => {
            const text = JSON.parse(e.data);
            result_output.textContent += text;
            result_output.scrollTop = result_output.scrollHeight;
        };

        es.addEventListener("done", (e) => {
            const ok = parseInt(e.data, 10) === 0;
            render_result(ok);
            loading_el.style.display = "none";
            run_btn.disabled = false;
            es.close();
            es = null;
        });

        es.onerror = () => {
            if (!es || es.readyState === EventSource.CLOSED) {
                loading_el.style.display = "none";
                run_btn.disabled = false;
                es = null;
            }
        };
    };

    run_btn.addEventListener("click", run);
})();
</script>

<?php require SITE_FOOTER; ?>
