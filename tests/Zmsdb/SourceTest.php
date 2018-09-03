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

        $entity2 = (new Query())->readEntity('dldb', 2);
        $this->assertArrayHasKey('data', $entity2->getProviderList()->getFirst());
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

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input, 1);
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals('Dienstleistungsdatenbank', $entity->getLabel());
        $entity->label = 'Dienstleistungsdatenbank Update';
        $entity->editable = true;
        $entity = $query->updateEntity($entity, 1);
        $this->assertEquals('Dienstleistungsdatenbank Update', $entity->getLabel());
        $this->assertTrue($entity->isEditable());
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
}
