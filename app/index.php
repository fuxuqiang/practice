<?php

require __DIR__.'/../app.php';

if ($cors = config('common')['cors']) {
    header('Access-Control-Allow-Origin: '.$cors);
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header('Access-Control-Allow-Headers: Authorization');
        exit;
    }
}

require __DIR__.'/helpers.php';

$response = \src\Http::handle($_SERVER, $_REQUEST);

if (!is_null($response)) {
    header('Content-Type: application/json');
    echo json_encode($response);
}