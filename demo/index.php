<?php

$pos = strpos($_SERVER['REQUEST_URI'], '?');
$file = $pos === false ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $pos);

if (is_file('.' . $file)) {
    if (preg_match('/.*\.php$/', $file)) {
        header('Content-Type: text/html; charset=utf-8');
        include '.' . $file;
    } else if (preg_match('/.*\.js$/', $file)) {
        header('Content-Type: application/javascript');
        echo file_get_contents('.' . $file);
    } else if (preg_match('/.*\.css$/', $file)) {
        header('Content-Type: text/css');
        echo file_get_contents('.' . $file);
    } else {
        echo file_get_contents('.' . $file);
    }
    exit;
}

include 'layout.php';
