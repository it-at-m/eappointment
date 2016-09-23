<?php

namespace BO\Zmsentities\Tests;

class SessionTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Session';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->getSerializedContent();
        $unserializedEntity = $entity->getUnserializedContent();
        $content = $unserializedEntity->getContent();
        $basket = $entity->getBasket();
        $human = $entity->getHuman();

        $this->assertTrue(is_array($content), 'session content missed');
        $this->assertTrue(is_array($basket), 'basket conten not unserialized');
        $this->assertTrue(is_array($human), 'human content not unserialized');
        $this->assertTrue('kck6Qz' == $human['captcha_text'], 'human not accessible');
        $this->assertTrue($entity->hasRequests(), 'requests not accessible');
        $this->assertTrue($entity->hasProvider(), 'providers not accessible');
        $this->assertTrue($entity->hasScope(), 'scope not accessible');
        $this->assertFalse($entity->hasConfirmationNotification(), 'confirmation notification should not be set');
        $this->assertTrue($entity->hasAuthKey(), 'authKey should be set');
        $this->assertTrue($entity->hasDate(), 'date should be set');
        $this->assertTrue($entity->hasEntryValues(), 'entry data does not exists');

        $entity->removeLastStep();
        $lastStep = $entity->getLastStep();
        $this->assertFalse('dayselect' == $lastStep, 'last step not removed successfully');
    }

    public function testStatus()
    {
        $entity = new $this->entityclass();
        $entity->content = null;
        $this->assertTrue($entity->isEmpty(), 'session is not empty');

        $entity = (new $this->entityclass())->getExample();
        $this->assertFalse($entity->isEmpty(), 'session is empty');
        $this->assertFalse($entity->isConfirmed(), 'session should not be confirmed');
        $this->assertFalse($entity->isProcessDeleted(), 'session should not be reserved');
        $this->assertFalse($entity->hasChangedProcess(), 'status should not be processChanged');
        $this->assertFalse($entity->hasPreviousAppointmentSearch(), 'status should not be inProgress');

        $this->assertFalse($entity->isReserved(), 'session should not be reserved');
        $entity->content['status'] = 'reserved';
        $entity->content['task'] = null;
        $this->assertTrue($entity->isReserved(), 'session should be reserved');
    }
}
