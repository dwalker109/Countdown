<?php
require __DIR__ . '/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;

$app->get('/', function () {
    return 'Hello World!';
});

$app->run();
