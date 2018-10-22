<?php

namespace BO\Zmsentities\Tests;

class SourceTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Source';

    public $collectionclass = '\BO\Zmsentities\Collection\SourceList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals('Dienstleistungsdatenbank', $entity->getLabel());
        $this->assertFalse($entity->isEditable());
        $this->assertTrue($entity->getContact() instanceof \BO\Zmsentities\Contact);
        $this->assertTrue($entity->getProviderList() instanceof \BO\Zmsentities\Collection\ProviderList);
        $this->assertTrue($entity->getRequestList() instanceof \BO\Zmsentities\Collection\RequestList);
        $this->assertFalse($entity->isCompleteAndEditable());
    }

    public function testProvider()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->providers = array((new \BO\Zmsentities\Provider())->getExample()->getArrayCopy());
        $this->assertTrue($entity->hasProvider('21334'), 'ProviderId does not exists');
    }

    public function testRequest()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->requests = array((new \BO\Zmsentities\Request())->getExample()->getArrayCopy());
        $this->assertTrue($entity->hasRequest('120335'), 'RequestId does not exists');
    }

    public function testRequestRelation()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->getRequestRelationList()->hasRequest(120335));
        $this->assertFalse($entity->getRequestRelationList()->hasRequest(123456));
    }

    public function testIsEditable()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->editable = true;
        $entity->requests = array((new \BO\Zmsentities\Request())->getExample()->getArrayCopy());
        $entity->providers = array((new \BO\Zmsentities\Provider())->getExample()->getArrayCopy());
        $this->assertTrue($entity->isCompleteAndEditable());
    }
}
