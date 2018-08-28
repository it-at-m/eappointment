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
    }

    public function testProvider()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->providers = array((new \BO\Zmsentities\Provider())->getExample());
        $this->assertTrue($entity->hasProvider('21334'), 'ProviderId does not exists');
    }

    public function testRequest()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->requests = array((new \BO\Zmsentities\Request())->getExample());
        $this->assertTrue($entity->hasRequest('120335'), 'RequestId does not exists');
    }
}
