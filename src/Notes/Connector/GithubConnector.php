<?php
namespace Notes\Connector;

class GithubConnector extends BaseConnector {
    protected $client=null;
    protected $personalAccessToken=null;

    public function __construct($personal_access_token = null)
    {
        if($personal_access_token) {
            $this->personalAccessToken = $personal_access_token;
        }
    }
    public function getClient()
    {
        if(!$this->client) {
            $this->client = new \Github\Client();
            if($this->personalAccessToken) {
                $this->client->authenticate(
                    $this->personalAccessToken,
                    null,
                    \Github\Client::AUTH_URL_TOKEN
                );
            }
        }



        return $this->client;
    }

    public function getCommit($params) {

        try {
            $commitObj = $this->getClient()->api('repo')
            ->commits()
            ->show($params['repoOwner'], $params['repoName'], $params['commitSHA']);
        } catch (NetworkException  $e) {
            die("Cannot connect");
        } catch(\Exception $e) {
            throw $e;
        }
        return $commitObj;
    }
}