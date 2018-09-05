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

    public function testWithRequestRelations()
    {
        $entity = (new Query())->readEntity('dldb', 122280, 1);
        $this->assertEquals(58, $entity->getRequestRelationList()->getRequestList()->count());
        $this->assertArrayHasKey('$ref', $entity->getRequestRelationList()->getFirst()->request);

        $entity2 = (new Query())->readEntity('dldb', 122280, 2);
        $this->assertArrayHasKey('name', $entity2->getRequestRelationList()->getFirst()->request);
    }

    public function testEmptyDldbData()
    {
        $entity = (new Query())->readEntity('dldb', 122280, 0);
        $this->assertEntity("\\BO\\Zmsentities\\Provider", $entity);
    }

    public function testReadAssignedList()
    {
        $query = new Query();
        $collection = $query->readList('dldb', 1, true); // resolveReferences = 1 and addConditionIsAssigned = true
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertTrue($collection->hasEntity('122251')); // Bürgeramt Schöneberg has assigned department
        $this->assertFalse($collection->hasEntity('121364')); // Kfz-Zulassungsbehörde-Friedr.-Kreuzberg without
                                                              // assigned department
    }

    public function testReadNotAssignedList()
    {
        $query = new Query();
        $collection = $query->readList('dldb', 1, false); // resolveReferences = 1 and addConditionIsAssigned = false
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertFalse($collection->hasEntity('122251')); // Bürgeramt Schöneberg without assigned department
        $this->assertTrue($collection->hasEntity('121364')); // Kfz-Zulassungsbehörde-Friedr.-Kreuzberg has
                                                             // assigned department
    }

    public function testReadListByRequest()
    {
        $query = new Query();
        $collection = $query->readListByRequest('dldb', '120335');
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertTrue($collection->hasEntity('122286')); // Bürgeramt Sonnenallee
        $collection = $query->readListByRequest('dldb', '99999999999999999'); // unknown request
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertFalse($collection->hasEntity('122286')); // Bürgeramt Sonnenallee
    }

    public function testWriteImport()
    {
        $query = new Query();
        $repository = (new \BO\Dldb\FileAccess())->loadFromPath(\BO\Zmsdb\Source\Dldb::$importPath);
        $importInput = $repository->fromLocation()->fetchList();
        $collection = $query->writeImportList($importInput, 'dldb'); //return written entity by true
        $this->assertEquals('dldb', $collection->getFirst()->getSource());
        $this->assertEquals(121362, $collection->getFirst()->getId());
    }

    public function testWriteRequestRelationImport()
    {
        $query = new \BO\Zmsdb\RequestRelation();
        $repository = (new \BO\Dldb\FileAccess())->loadFromPath(\BO\Zmsdb\Source\Dldb::$importPath);
        $importInput = $repository->fromLocation()->fetchList();
        $collection = $query->writeImportList($importInput, 'dldb'); //return written entity by true
        $this->assertEquals('dldb', $collection->getFirst()->getSource());
        $this->assertEquals('0', $collection->getFirst()->getSlotCount());
        $this->assertEquals(122208, $collection->getFirst()->getProvider()->getId());
        $this->assertEquals(120335, $collection->getFirst()->getRequest()->getId());
    }
}
