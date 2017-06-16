<?php

namespace BO\Zmsentities\Tests;

class NotificationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Notification';
    public $collectionclass = '\BO\Zmsentities\Collection\NotificationList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->client = $entity->getFirstClient();
        $entity->addScope((new \BO\Zmsentities\Scope())->getExample());
        $this->assertTrue(123 == $entity->getScopeId(), 'Getting scope id failed');
        $this->assertTrue(123456 == $entity->getProcessId(), 'Getting process id failed');
        $this->assertTrue(74 == $entity->getDepartmentId(), 'Getting department id failed');
        $this->assertTrue('abcd' == $entity->getProcessAuthKey(), 'Getting authKey failed');
        $this->assertContains('Denken Sie an ihren Termin', $entity->getMessage(), 'Getting message failed');
        $this->assertContains('terminvereinbarung@', $entity->getIdentification(), 'Getting message failed');
        $this->assertEquals('Max Mustermann', $entity->getFirstClient()->familyName, 'Getting first client failed');
        $this->assertEquals(
            'SMS=030115@sms.verwalt-berlin.de',
            $entity->getRecipient(),
            'Getting recipient number failed'
        );
    }

    public function testToCustomMessageEntity()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $formCollection = array(
            'message' => \BO\Mellon\Validator::value('Das ist eine Testnachricht')->isString()->isBiggerThan(2)
        );
        $formCollection = \BO\Mellon\Validator::collection($formCollection);
        $entity = $entity->toCustomMessageEntity($process, $formCollection->getValues());
        $this->assertEquals('Das ist eine Testnachricht', $entity->message);
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
        $this->assertTrue($entity->hasProperties('id', 'department', 'process'), 'properties missed, please check');
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
        $process['queue']['withAppointment'] = 0;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();

        $resolvedEntity = $entity->toResolvedEntity($process, $config, $department);
        $this->assertContains(
            'Ihre Telefonnummer wurde erfolgreich registriert',
            $resolvedEntity['message'],
            'resolving entity failed'
        );
    }

    public function testToResolvedEntityAppointment()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process['queue']['withAppointment'] = 1;
        $process['id'] = 4567;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();

        $resolvedEntity = $entity->toResolvedEntity($process, $config, $department);
        $this->assertContains(
            'Ihr Termin: Vorgangsnr. 4567',
            $resolvedEntity['message'],
            'resolving entity failed'
        );
    }
}
