<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app->get('/hello/{name}', function($name) use($app) {
    return 'Hello '.$app->escape($name);
});

$app->post('/push',function(Request $request) use $app{
    $message = $request->get('message');

    return new Response('Thank you for your feedback!', 201);
});

$app->run();