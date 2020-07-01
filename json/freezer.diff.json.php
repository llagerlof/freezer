<?php
error_reporting(error_reporting() & ~E_WARNING);
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../class/Freezer.php';

$config = $_GET['config'];
$Freezer = new Freezer($config);
$diff = $Freezer->load();
$response = $Freezer->getResponse(Freezer::DIFF);

echo json_encode($response);
