<?php

namespace BO\Zmsbackend\Tests\FetchFailed\Service;

use \BO\Zmsbackend\Provider\Service\Provider as Provider;
use \BO\Zmsbackend\Provider\Repository\Provider as Query;
use \BO\Zmsentities\Provider as Entity;

class FetchFailedTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testFetchOneFailed()
    {
        $this->expectException('\BO\Zmsbackend\Exception\Pdo\PdoFailed');
        $provider = new \BO\Zmsbackend\Provider\Service\Provider();
        $query = new Query('SELECT');
        $Provider = $provider->fetchOne($query, new Entity());
    }

    public function testFetchListFailed()
    {
        $this->expectException('\BO\Zmsbackend\Exception\Pdo\PdoFailed');
        $provider = new \BO\Zmsbackend\Provider\Service\Provider();
        $query = new Query('SELECT');
        $Provider = $provider->fetchList($query, new Entity());
    }

    public function testStatementFailed()
    {
        $this->expectException("\BO\Zmsbackend\Exception\Pdo\PdoFailed");
        $provider = new \BO\Zmsbackend\Provider\Service\Provider();
        $statement = $provider->fetchStatement(new Query('SELECT'));
    }
}
