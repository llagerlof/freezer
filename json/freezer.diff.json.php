<?php
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../class/ResponseJson.php';
require_once dirname(__FILE__) . '/../class/Freezer.php';

$ResponseJson = new ResponseJson();

// Tests if it is a POST
if (!isset($_POST['config'])) {
    $ResponseJson->setError('Missing POST data.');
    die(json_encode($ResponseJson->getResponse(Freezer::FREEZE)));
}

// Tests if POST data is a string
if (!is_string($_POST['config'])) {
    $ResponseJson->setError('Invalid POST data.');
    die(json_encode($ResponseJson->getResponse(Freezer::FREEZE)));
}

// Tests if config string is too large
if (strlen($_POST['config']) > 255) {
    $ResponseJson->setError('POST data too big.');
    die(json_encode($ResponseJson->getResponse(Freezer::FREEZE)));
}

// Sanitize input
$config = preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['config']);

// Tests if there are any string left after cleaning
if (empty($config)) {
    $ResponseJson->setError('Empty POST data after sanitization.');
    die(json_encode($ResponseJson->getResponse(Freezer::FREEZE)));
}

$Freezer = new Freezer($config);

if (!$Freezer->getErrors()) {
    $diff = $Freezer->load();
}

$response = $Freezer->getResponse(Freezer::DIFF);

echo json_encode($response);
