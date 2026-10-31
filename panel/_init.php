<?php

require '/miz/miz.php';

define('SITE_HEADER', __DIR__ . '/_header.php');
define('SITE_FOOTER', __DIR__ . '/_footer.php');

function t_success($msg) {
    echo '<div class="alert alert-success">';
    echo $msg;
    echo '</div>';
}

function run_miz_bin(string $script, array $args = []): bool
{
    $cmd = '/miz/bin/' . $script;
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg((string) $arg);
    }

    passthru($cmd, $exit_code);
    return $exit_code === 0;
}

function run_miz_bin_from_web(string $script, array $args = [], ?string &$std_output = null): bool
{
    $cmd = '/miz/bin/' . $script;
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg((string) $arg);
    }

    $lines = [];
    exec($cmd . ' 2>&1', $lines, $exit_code);
    $std_output = implode("\n", $lines);

    return $exit_code === 0;
}
