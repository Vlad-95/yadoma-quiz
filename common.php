<?php

function log_to_file($message) {
    $config = include('.config.php');

    $log_message = date('d.m.y H:i:s — ') . $message . PHP_EOL;
    $log_file = $config['log_file_location'];

    error_log($log_message, 3, $log_file);
}

?>
