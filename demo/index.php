<?php

$pos = strpos($_SERVER['REQUEST_URI'], '?');
$file = $pos === false ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $pos);

if (is_file('.' . $file)) {
    if (preg_match('/.*\.php$/', $file))
        include '.' . $file;
    else
        echo file_get_contents('.' . $file);
    exit;
}

include 'layout.php';
