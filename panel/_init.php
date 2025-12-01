<?php

require __DIR__ . '/../miz.php';

function t_error($msg) {
    echo '<div class="alert alert-danger">';
    echo is_array($msg) ?  $msg[0] : $msg;
    echo '</div>';
}

function t_success($msg) {
    echo '<div class="alert alert-success">';
    echo $msg;
    echo '</div>';
}

