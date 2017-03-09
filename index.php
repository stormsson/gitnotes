<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Http\Client\Exception\NetworkException;

use Notes\Parser\NoteParser;
use Notes\Connector\GithubConnector;


function getConnector() {
    return new GithubConnector();
}

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/hello/{name}', function($name) use($app) {
    return 'Hello '.$app->escape($name);
});

/**

@noteTags tag1,tag2
@noteTitle titolo della nota
*/

$app->post('/push',function(Request $request) use ($app){


    $client = new \Github\Client();
    $noteParser = new NoteParser();

    $connector = getConnector();

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

        try {
            $commitObj = $connector->getCommit([
                'repoOwner'=>$repoOwner,
                'repoName' =>$repoName,
                'commitSHA' =>$commitSHA
                ]);
        } catch (NetworkException  $e) {
            die("Cannot connect");
        } catch(\Exception $e) {
            throw $e;
        }

        foreach ($commitObj['files'] as $file) {
            $filePatch = $file['patch'];
            $fileName = $file['filename'];

            /**
            @noteTitle Questa e' una prova
            @noteTags tag1, tag2, tag3
            */
            $noteTitle = $noteParser->parseTitle($filePatch);
            $noteTags = $noteParser->parseTags($filePatch);
        }
        die(var_dump($noteTitle, $noteTags));
    }

    return new Response('Thank you for your feedback!', 201);
});

$app->run();