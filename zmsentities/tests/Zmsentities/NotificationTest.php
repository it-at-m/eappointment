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
        $this->assertTrue(123456 == $entity->getProcess()->id, 'Getting process id failed');
        $this->assertTrue(74 == $entity->getDepartmentId(), 'Getting department id failed');
        $this->assertTrue('abcd' == $entity->getProcessAuthKey(), 'Getting authKey failed');
        $this->assertStringContainsString('Denken Sie an ihren Termin', $entity->getMessage(), 'Getting message failed');
        $this->assertStringContainsString('terminvereinbarung@', $entity->getIdentification(), 'Getting message failed');
        $this->assertEquals('Max Mustermann', $entity->getFirstClient()->familyName, 'Getting first client failed');
        $this->assertEquals('030 115', $entity->getClient()->telephone, 'Wrong telephone number');
        $this->assertEquals(
            'SMS=+4930115@sms.verwalt-berlin.de',
            $entity->getRecipient(),
            'Getting recipient number failed'
        );
        $this->assertEquals('2015-11-19', $entity->getCreateDateTime()->format('Y-m-d'), 'Wrong date');
    }

    public function testRecipientWithDefaultCountryCode()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->client = $entity->getFirstClient();
        $entity->client->telephone = '0123 456 789 10';
        $entity->addScope((new \BO\Zmsentities\Scope())->getExample());
        $this->assertEquals(
            'SMS=+4912345678910@sms.verwalt-berlin.de',
            $entity->getRecipient(),
            'Getting recipient number failed'
        );
    }

    public function testRecipientWithIDD()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->client = $entity->getFirstClient();
        $entity->client->telephone = '0049 0123 456 789 10';
        $entity->addScope((new \BO\Zmsentities\Scope())->getExample());
        $this->assertEquals(
            'SMS=+4912345678910@sms.verwalt-berlin.de',
            $entity->getRecipient(),
            'Getting recipient number failed'
        );
    }

    public function testRecipientWithPlusSign()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->client = $entity->getFirstClient();
        $entity->client->telephone = '+49 0123 456 789 10';
        $entity->addScope((new \BO\Zmsentities\Scope())->getExample());
        $this->assertEquals(
            'SMS=+4912345678910@sms.verwalt-berlin.de',
            $entity->getRecipient(),
            'Getting recipient number failed'
        );
    }

    public function testGetRecipientFailed()
    {
        $this->expectException('\BO\Zmsentities\Exception\NotificationMissedNumber');
        $entity = (new $this->entityclass())->getExample();
        unset($entity->client);
        $entity->getRecipient();
    }

    public function testToCustomMessageEntity()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();
        $formCollection = array(
            'message' => \BO\Mellon\Validator::value('Das ist eine Testnachricht')->isString()->isBiggerThan(2)
        );
        $formCollection = \BO\Mellon\Validator::collection($formCollection);
        $entity = $entity->toCustomMessageEntity($process, $formCollection->getValues(), $department);
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
        unset($entity['client']);
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process['queue']['withAppointment'] = 0;
        $process->getFirstAppointment()->setDateByString('2016-04-01 00:00');
        $config = (new \BO\Zmsentities\Config())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();

        $resolvedEntity = $entity->toResolvedEntity($process, $config, $department, 'confirmed');
        $this->assertStringContainsString(
            'Ihr Termin wurde erfolgreich gebucht mit der Nummer:  123',
            $resolvedEntity['message'],
            'resolving entity failed'
        );

        unset($process->scope->preferences);
        $resolvedEntity = $entity->toResolvedEntity($process, $config, $department, 'confirmed');
        $this->assertStringContainsString(
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

        $resolvedEntity = $entity->toResolvedEntity($process, $config, $department, 'appointment');
        $this->assertStringContainsString(
            'Ihr Termin: Vorgangsnr. 4567',
            $resolvedEntity['message'],
            'resolving entity failed'
        );
    }
}
