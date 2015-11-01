<?php
require __DIR__ . '/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;

// Register Twig for views
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

/**
 * Show intro page/upload form
 */
$app->get('/', function () use ($app) {

    return $app['twig']->render(
        'upload.html.twig'
    );

});

/**
 * Process uploaded CSV
 */
$app->post('/', function (Request $request) use ($app) {

    $data_to_process = [];
    $results = [];

    // Stop calculating after this many successful matches - too high and RAM will run out
    $max_matches = 10;

    // Retrieve the uploaded CSV and move the contents
    $csv = $request->files->get('csv');

    if (!$csv) {
        // Render an error if no file to move
        return $app['twig']->render(
            'error.html.twig',
            compact('results')
        );
    } else {
        $csv = $csv->move(
            $app['upload_folder'],
            uniqid()
        );
    }

    // Read the CSV data and iterate each line to ready for processing
    if (($handle = fopen($csv->getRealPath(), "r")) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            // Cast all data to integers, seperate the final value (target), keep a display string
            // version of the source numbers as well
            $numbers = array_map('intval', $data);
            $target = array_pop($numbers);
            $numbers_str = implode(', ', $numbers);
            $data_to_process[] = compact('numbers', 'target', 'numbers_str');
        }
        fclose($handle);
        unlink($csv->getRealPath());
    }

    // Solve the problem(s)
    foreach ($data_to_process as $problem) {
        $solver = new dwalker109\Countdown\Solver($problem['numbers'], $problem['target'], $max_matches);
        $results[] = [
            'problem' => $problem,
            'expressions' => $solver->run(),
        ];
    }

    // Render
    return $app['twig']->render(
        'results.html.twig',
        compact('results', 'max_matches')
    );
});

$app->run();
