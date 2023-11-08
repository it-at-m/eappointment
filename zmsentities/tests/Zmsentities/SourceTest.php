<?php

namespace BO\Zmsentities\Tests;

class SourceTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Source';

    public $collectionclass = '\BO\Zmsentities\Collection\SourceList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->save = 'submit';
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals('Dienstleistungsdatenbank', $entity->getLabel());
        $this->assertFalse($entity->isEditable());
        $this->assertTrue($entity->getContact() instanceof \BO\Zmsentities\Contact);
        $this->assertTrue($entity->getProviderList() instanceof \BO\Zmsentities\Collection\ProviderList);
        $this->assertTrue($entity->getRequestList() instanceof \BO\Zmsentities\Collection\RequestList);
        $this->assertFalse($entity->isCompleteAndEditable());
    }

    public function testWithDataObject()
    {
        // string
        $entity = (new $this->entityclass())->getExample();
        $entity->save = 'submit';
        $entity->providers[0]['data'] = '{"json":"data","key":"value"}';
        $entity->requests[0]['data'] = '{"json":"data","key":"value"}';
        $entity = $entity->withCleanedUpFormData();
        $this->assertTrue(is_object($entity->getProviderList()->getFirst()->getAdditionalData()));
        $this->assertTrue(is_object($entity->getRequestList()->getFirst()->getAdditionalData()));
        $this->assertEquals('data', $entity->getRequestList()->getFirst()->getAdditionalData()->json);
        $this->assertEquals('value', $entity->getRequestList()->getFirst()->getAdditionalData()->key);

        // array
        $entity = (new $this->entityclass())->getExample();
        $entity->save = 'submit';
        $entity->providers[0]['data'] = ['test' => 1];
        $entity->requests[0]['data'] = ['test' => 1];
        $entity = $entity->withCleanedUpFormData();
        $this->assertTrue(is_object($entity->getProviderList()->getFirst()->getAdditionalData()));
        $this->assertTrue(is_object($entity->getRequestList()->getFirst()->getAdditionalData()));
        $this->assertEquals(1, $entity->getProviderList()->getFirst()->getAdditionalData()->test);
        $this->assertEquals(1, $entity->getRequestList()->getFirst()->getAdditionalData()->test);

        // empty
        $entity = (new $this->entityclass())->getExample();
        $entity->save = 'submit';
        $entity->providers[0]['data'] = null;
        $entity->requests[0]['data'] = null;
        $entity = $entity->withCleanedUpFormData();
        $this->assertNull($entity->getProviderList()->getFirst()->getAdditionalData());
        $this->assertNull($entity->getRequestList()->getFirst()->getAdditionalData());
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
        $this->assertTrue($entity->getRequestRelationList()->hasProvider(21334));
        $this->assertFalse($entity->getRequestRelationList()->hasProvider(123456));
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
