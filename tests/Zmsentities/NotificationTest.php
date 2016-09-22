<?php

namespace BO\Zmsentities\Tests;

class NotificationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Notification';
    public $collectionclass = '\BO\Zmsentities\Collection\NotificationList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addScope((new \BO\Zmsentities\Scope())->getExample());
        $this->assertTrue(123 == $entity->getScopeId(), 'Getting scope id failed');
        $this->assertTrue(123456 == $entity->getProcessId(), 'Getting process id failed');
        $this->assertTrue(123 == $entity->getDepartmentId(), 'Getting department id failed');
        $this->assertTrue('abcd' == $entity->getProcessAuthKey(), 'Getting authKey failed');
        $this->assertContains('Denken Sie an ihren Termin', $entity->getMessage(), 'Getting message failed');
        $this->assertContains('terminvereinbarung@', $entity->getIdentification(), 'Getting message failed');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue($collection->hasEntity(1234), "Missing Test Entity with ID 1234 in collection");
    }

    public function testHasProperties()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasProperties('id','department','process'), 'properties missed, please check');
        try {
            $entity->hasProperties('no_property');
            $this->fail("Expected exception NotificationMissedProperty not thrown");
        } catch (\BO\Zmsentities\Exception\NotificationMissedProperty $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testToResolvedEntity()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();

        $resolvedEntity = $entity->toResolvedEntity($process, $config);
        $this->assertContains(
            'Ihre Telefonnummer wurde erfolgreich registriert',
            $resolvedEntity['message'],
            'resolving entity failed'
        );
    }
}
