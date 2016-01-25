<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Request as Query;

class RequestTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 120335);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
