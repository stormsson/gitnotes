<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Http\Client\Exception\NetworkException;


$app = new Silex\Application();
$app['debug'] = true;

$app->get('/hello/{name}', function($name) use($app) {
    return 'Hello '.$app->escape($name);
});

/**

@noteTags tag1,tag2
@noteTitle titolo della nota
*/

const NOTE_TAGS="@noteTags";
const NOTE_TITLE="@noteTitle";


function parseTags($text)
{
    $matches=[];
    $result = false;
    $noteTagsExpr = "/".NOTE_TAGS." (.*?)\n/i";
    if(preg_match($noteTagsExpr, $text, $matches)) {
        $matches = explode(",", $matches[1]);
        foreach ($matches as $tag) {
            $result[] = trim($tag);
        }
    }

    return $result;
}

function parseTitle($text) {
    $matches=[];
    $result = false;
    $noteTagsExpr = "/".NOTE_TITLE." (.*?)\n/i";
    if(preg_match($noteTagsExpr, $text, $matches)) {
        $result = $matches[1];
    }

    return $result;
}



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

        try {
            $commitObj = $client->api('repo')
            ->commits()
            ->show($repoOwner, $repoName, $commitSHA);
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
            $noteTitle = parseTitle($filePatch);
            $noteTags = parseTags($filePatch);
        }

        die(var_dump($noteTitle, $noteTags, $commitObj));
    }



    $payload = $request->request->all();

    return new Response('Thank you for your feedback!', 201);
});

$app->run();