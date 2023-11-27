<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Provider as Provider;
use \BO\Zmsdb\Query\Provider as Query;
use \BO\Zmsentities\Provider as Entity;

class FetchFailedTest extends Base
{
    public function testFetchOneFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Pdo\PdoFailed');
        $provider = new Provider();
        $query = new Query('SELECT');
        $Provider = $provider->fetchOne($query, new Entity());
    }

    public function testFetchListFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Pdo\PdoFailed');
        $provider = new Provider();
        $query = new Query('SELECT');
        $Provider = $provider->fetchList($query, new Entity());
    }

    public function testStatementFailed()
    {
        $this->expectException("\BO\Zmsdb\Exception\Pdo\PdoFailed");
        $provider = new Provider();
        $statement = $provider->fetchStatement(new Query('SELECT'));
    }
}
