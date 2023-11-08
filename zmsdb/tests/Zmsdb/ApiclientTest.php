<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Apiclient as Query;
use \BO\Zmsdb\Apiquota as QuotaQuery;
use \BO\Zmsentities\Apiclient as Entity;

class ApiclientTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity('default');
        $this->assertEntity("\\BO\\Zmsentities\\Apiclient", $entity);

        $this->assertEquals('default', $entity->clientKey);
        $this->assertEquals('default', $entity->shortname);
    }

    public function testBlocked()
    {
        $query = new Query();
        $entity = $query->readEntity('8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE');
        $this->assertEntity("\\BO\\Zmsentities\\Apiclient", $entity);

        $this->assertEquals('blocked', $entity->accesslevel);
    }
}
