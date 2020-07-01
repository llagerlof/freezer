<?php
require_once dirname(__FILE__) . '/../class/Freezer.php';

header('Content-Type: application/json');

$config = $_GET['config'];
$Freezer = new Freezer($config);
$saved = $Freezer->save();
$response = $Freezer->getResponse(Freezer::FREEZE);

echo json_encode($response);
