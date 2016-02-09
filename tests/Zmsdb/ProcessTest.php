<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;

class ProcessTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity(1049, 'f3b3');
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertEntity("\\BO\\Zmsentities\\Process", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testProcessFree()
    {
        $entity = (new Query())->readEntity(1049, 'f3b3');
        $this->assertEntity("\\BO\\Zmsentities\\Process", $entity);
    }
}
