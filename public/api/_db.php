<?php
if (!function_exists('json_fatal_guard')) {
    function json_fatal_guard($logfile) {
        set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logfile) {
            file_put_contents($logfile, date('[Y-m-d H:i:s] ')."$errstr in $errfile:$errline\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error'=>'internal error']);
            exit;
        });
        set_exception_handler(function($e) use ($logfile) {
            file_put_contents($logfile, date('[Y-m-d H:i:s] ').$e->getMessage()."\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error'=>'internal error']);
            exit;
        });
    }
}

function db() {
    $mysqli = new mysqli("localhost", "kancir_cosmetics", "Mm@53545556", "kancir_cosmetics"); // اطلاعات اتصال دیتابیس را تنظیم کن
    if ($mysqli->connect_errno) {
        throw new Exception("DB Connection failed: ".$mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}