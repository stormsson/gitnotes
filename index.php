<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

$app = new Silex\Application();

$app->get('/hello/{name}', function($name) use($app) {
    return 'Hello '.$app->escape($name);
});

$app->post('/push',function(Request $request) use ($app){
    $client = new \Github\Client();


    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);

    } else {
        throw new AccessDeniedHttpException("403"   );
    }

    $repoName = $data['repository']['name'];
    $repoOwner = $data['repository']['owner']['name'];


    foreach ($data['commits'] as $commit) {
        $commitSHA = $commit['id'];
        $author = $commit['author'];

        $commitObj = $client->api('repo')
            ->commits()
            ->show($repoOwner, $repoName, $commitSHA);

        foreach ($commitObj['files'] as $file) {
            $filePatch = $file['patch'];
            $fileName = $file['filename'];
        }
        die(var_dump($commitObj));



    }



    $payload = $request->request->all();

    return new Response('Thank you for your feedback!', 201);
});

$app->run();