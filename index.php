<?php
require_once __DIR__.'/vendor/autoload.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Http\Client\Exception\NetworkException;

use Notes\Parser\NoteParser;
use Notes\Connector\GithubConnector;

use SpreadsheetManager\XlsManager;

const SPREADSHEET_NAME = "gitNotes";

function getConnector() {
    $githubPersonalAccessToken = file_get_contents(__DIR__."/data/github_personal_access_token");

    if(false ===  $githubPersonalAccessToken) {
        throw new \Exception("github_personal_access_token Not found!");
    }

    return new GithubConnector($githubPersonalAccessToken);
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

    $xlsManager = new XlsManager(__DIR__."/data/google_credentials.json");
    $xlsManager->open(SPREADSHEET_NAME);
    // $xlsManager->debug();

    $connector = getConnector();

    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);

    } else {
        throw new AccessDeniedHttpException("403"   );
    }

    $repoOwner = $data['repository']['owner']['name'];
    $repoName = $data['repository']['name'];
    $repoFullName = $data['repository']['full_name'];
    $repoUrl = $data['repository']['url'];
    $repoDescription = $data['repository']['description'];


    $alteredRows = 0;
    foreach ($data['commits'] as $commit) {
        $commitSHA = $commit['id'];
        $author = $commit['author'];
        $commitUrl = $commit['url'];
        $commitTimestamp = $commit['timestamp'];

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
            // die(var_dump($commitObj, $file));
            $filePatch = $file['patch'];
            $fileName = $file['filename'];
            $fileUrl = $file['blob_url'];
            $fileRawUrl = $file['raw_url'];
            // die(var_dump($commitObj));

            /**
            @noteTitle Questa e' una prova nuova
            @noteTags tag1, tag2, tag3, nuovotag
            */
            if($noteParser->isParsable($fileName)) {
                $noteTitle = $noteParser->parseTitle($filePatch);
                $noteTags = $noteParser->parseTags($filePatch);
                die(var_dump($filePatch));
                if($noteTitle) {
                    $data = [
                    $commitTimestamp,
                    $author['name'],
                    $repoFullName,
                    $noteTitle,
                    $noteTags? implode(',',$noteTags) : "",
                    $fileName,
                    $fileUrl,
                    $fileRawUrl,
                    $commitUrl,
                    $repoOwner,
                    $repoName
                ];

                $xlsManager->insertOrUpdate($data);
                $alteredRows++;
                }

            }
        }
        // die(var_dump($noteTitle, $noteTags));
    }

    return new Response("Altered $alteredRows rows",200);
});

$app->run();