<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Provider as Query;

class ProviderTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 122280, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Provider", $entity);
        $this->assertArrayHasKey('data', (array) $entity);
    }

    public function testUnknowSource()
    {
        $this->expectException('BO\Zmsdb\Exception\Source\UnknownDataSource');
        (new Query())->readEntity('xxx', 122280, 1);
    }

    public function testEmptyDldbData()
    {
        $entity = (new Query())->readEntity('dldb', 122280, 0);
        $this->assertEntity("\\BO\\Zmsentities\\Provider", $entity);
    }

    public function testReadAssignedList()
    {
        $query = new Query();
        // resolveReferences = 1 and addConditionIsAssigned = true
        $collection = $query->readListBySource('dldb', 1, true);
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertTrue($collection->hasEntity('122251')); // Bürgeramt Schöneberg has assigned department
        $this->assertFalse($collection->hasEntity('121364')); // Kfz-Zulassungsbehörde-Friedr.-Kreuzberg without
                                                              // assigned department
    }

    public function testReadNotAssignedList()
    {
        $query = new Query();
        // resolveReferences = 1 and addConditionIsAssigned = false
        $collection = $query->readListBySource('dldb', 1, false);
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertFalse($collection->hasEntity('122251')); // Bürgeramt Schöneberg without assigned department
        $this->assertTrue($collection->hasEntity('121364')); // Kfz-Zulassungsbehörde-Friedr.-Kreuzberg has
                                                             // assigned department
    }

    public function testReadListFilteredByRequest()
    {
        $query = new Query();
        $collection = $query->readListBySource('dldb', 1, true, '120335');
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertTrue($collection->hasEntity('122286')); // Bürgeramt Sonnenallee
        $collection = $query->readListBySource('dldb', 1, true, '99999999999999999'); // unknown request
        $this->assertEntityList("\\BO\\Zmsentities\\Provider", $collection);
        $this->assertFalse($collection->hasEntity('122286')); // Bürgeramt Sonnenallee
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $entity = (new \BO\Zmsentities\Provider())->getExample();
        $entity = $query->writeEntity($entity);
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals(21334, $entity->getId());
    }

    public function testWriteEntityFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Provider\ProviderContactMissed');
        $query = new Query();
        $entity = (new \BO\Zmsentities\Provider())->getExample();
        unset($entity['contact']);
        $query->writeEntity($entity);
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
        $this->assertEquals('2', $collection->getFirst()->getSlotCount());
        $this->assertEquals(122217, $collection->getFirst()->getProvider()->getId());
        $this->assertEquals(1120703, $collection->getFirst()->getRequest()->getId());
    }
}
