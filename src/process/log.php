<?php
function logMessage($filename, $message) {
    $logFilePath = $_SERVER['DOCUMENT_ROOT'] . "/logs/" . $filename;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "$timestamp - $message\n";
    file_put_contents($logFilePath, $logMessage, FILE_APPEND);
}
?>