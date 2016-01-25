<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Scope as Query;

class ScopeTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity(141, 1);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertEntity("\\BO\\Zmsentities\\Scope", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testCluster()
    {
        $entityList = (new Query())->readByClusterId(109, 1);
        //var_dump(json_encode($entityList, JSON_PRETTY_PRINT));
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testProvider()
    {
        $entityList = (new Query())->readByProviderId(122217, 1);
        //var_dump(json_encode($entityList, JSON_PRETTY_PRINT));
        $this->assertEntityList("\\BO\\Zmsentities\\Scope", $entityList);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
