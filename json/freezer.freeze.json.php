<?php
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../class/Freezer.php';

$config = $_GET['config'];

$Freezer = new Freezer($config);
$saved = $Freezer->save();

echo json_encode($saved);
