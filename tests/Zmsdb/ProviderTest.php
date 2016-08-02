<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Provider as Query;

class ProviderTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 122280, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Provider", $entity);
        $this->assertEquals(true, array_key_exists('data', $entity));
    }

    public function testUnknownSource()
    {
        $this->setExpectedException("\\BO\\Zmsdb\\Exception\\UnknownDataSource");
        $entity = (new Query())->readEntity('test', 122280, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Provider", $entity);
    }

    public function testReadList()
    {
        $query = new Query();
        $collection = $query->readList('dldb');
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertEquals(true, $collection->hasEntity('121364')); //Kfz-Zulassungsbehörde-Friedr.-Kreuzberg
    }

    public function testReadListByRequest()
    {
        $query = new Query();
        $collection = $query->readListByRequest('dldb', '120335');
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertEquals(true, $collection->hasEntity('122210')); //Bürgeramt Halemweg (Außenstelle)

        $collection = $query->readListByRequest('dldb', '99999999999999999'); // unknown request
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertEquals(false, $collection->hasEntity('122210')); //Bürgeramt Halemweg (Außenstelle)
    }
}
