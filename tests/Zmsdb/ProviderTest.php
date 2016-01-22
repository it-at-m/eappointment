<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Provider as Query;

class ProviderTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 122280);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertInstanceOf("\\BO\\Zmsentities\\Provider", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
