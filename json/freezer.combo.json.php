<?php
header('Content-Type: application/json');
$configs = array();
$files = scandir(dirname(__FILE__) . '/../config');
$config_files = preg_grep('/freezer\.[a-z0-9]+\.php/', $files);
$configs = array();
foreach ($config_files as $file) {
    preg_match('/freezer\.(.*?)\.php/', $file, $match);
    $configs[] = $match[1];
}

echo json_encode($configs);
