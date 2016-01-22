<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Scope as Query;

class ScopeTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity(647);
        var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertInstanceOf("\\BO\\Zmsentities\\Scope", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
