<?php
require __DIR__ . '/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;

$app->get('/', function () {
    $numbers = [3, 4, 7, 8 , 12];
    $target = 532;

    // Inject the required service
    $app['solver'] = new dwalker109\Countdown\Solver($numbers, $target);

    return var_dump($app['solver']->run());
});

$app->run();
