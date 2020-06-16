<?php
header('Content-Type: application/json');
$configs = array();
$files = scandir(dirname(__FILE__) . '/../config');
$config_files = preg_grep('/^freezer\.[a-z0-9]+\.php$/', $files);
$configs = array();
if (count($config_files) > 1) {
    $index = array_search('freezer.example.php', $config_files);
    unset($config_files[$index]);
}
foreach ($config_files as $file) {
    preg_match('/freezer\.(.*?)\.php/', $file, $match);
    $configs[] = $match[1];
}

echo json_encode($configs);
