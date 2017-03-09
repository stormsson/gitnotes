<?php
namespace Notes\Connector;

class GithubConnector extends BaseConnector {
    protected $client=null;

    public function getClient()
    {
        if(!$this->client) {
            $this->client = new \Github\Client();
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