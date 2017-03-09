<?php
namespace Notes\Connector;

abstract class BaseConnector {
    abstract public function getCommit($params);
}