<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Cluster as Cluster;
use \BO\Zmsdb\Query\Cluster as Query;
use \BO\Zmsentities\Cluster as Entity;

class FetchFailedTest extends Base
{
    public function testFetchOneFailed()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\PdoFailed');
        $cluster = new Cluster();
        $query = new Query('SELECT');
        $cluster = $cluster->fetchOne($query, new Entity());
    }

    public function testFetchListFailed()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\PdoFailed');
        $cluster = new Cluster();
        $query = new Query('SELECT');
        $cluster = $cluster->fetchList($query, new Entity());
    }
}
