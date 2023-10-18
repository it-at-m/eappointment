<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Source as Query;
use \BO\Zmsentities\Source as Entity;

class SourceTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 1);
        $this->assertEntity("\\BO\\Zmsentities\\Source", $entity);
        $this->assertEquals('dldb', $entity->getSource());
    }

    public function testResolveReferences()
    {
        $entity = (new Query())->readEntity('dldb', 1);
        $this->assertArrayNotHasKey('data', $entity->getProviderList()->getFirst());
    }

    public function testResolveReferences2()
    {
        $entity = (new Query())->readEntity('dldb', 2);
        $this->assertArrayHasKey('data', $entity->getProviderList()->getFirst());
    }

    public function testSourceMissing()
    {
        $entity = (new Query())->readEntity('', 1);
        $this->assertEmpty($entity);
    }

    public function testCollection()
    {
        $collection = (new Query())->readList(1);
        $this->assertEntityList("\\BO\\Zmsentities\\Source", $collection);
    }

    public function testWithRequestRelations()
    {
        $entity = (new Query())->readEntity('dldb', 1);
        $this->assertEquals(static::$requestRelationCount, $entity->getRequestRelationList()->count());
        $this->assertEquals(653, $entity->getRequestList()->count());
        $this->assertEquals(static::$requestCount, $entity->getRequestRelationList()->getRequestList()->count());
        $this->assertEquals(114, $entity->getRequestRelationList()->getProviderList()->count());
        $this->assertArrayHasKey('$ref', $entity->getRequestRelationList()->getFirst()->request);
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $entity->label = 'Dienstleistungsdatenbank Update';
        $entity->editable = true;

        $entity->providers = new \BO\Zmsentities\Collection\ProviderList();
        $entity->providers->addEntity((new \BO\Zmsentities\Provider())->getExample());
        $entity->requests = new \BO\Zmsentities\Collection\RequestList();
        $entity->requests->addEntity((new \BO\Zmsentities\Request())->getExample());

        $entity = $query->writeEntity($entity, 2);

        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals('Dienstleistungsdatenbank Update', $entity->getLabel());
        $this->assertTrue($entity->isEditable());
        $this->assertEquals(2, $entity->getRequestRelationList()->getFirst()->getSlotCount());
    }

    public function testWriteWithoutRequestRelations()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $entity->label = 'Dienstleistungsdatenbank Update';
        $entity->editable = true;
        unset($entity['requestrelation']);

        $provider = (new \BO\Zmsentities\Provider())->getExample();
        $entity->providers = new \BO\Zmsentities\Collection\ProviderList();
        $entity->providers->addEntity($provider);
        $entity->requests = new \BO\Zmsentities\Collection\RequestList();
        $entity->requests->addEntity((new \BO\Zmsentities\Request())->getExample());

        $entity = $query->writeEntity($entity, 2);

        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals('Dienstleistungsdatenbank Update', $entity->getLabel());
        $this->assertTrue($entity->isEditable());
        $this->assertEquals(1, $entity->getRequestRelationList()->getFirst()->getSlotCount());
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
