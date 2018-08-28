<?php

namespace BO\Zmsentities\Tests;

class SessionTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Session';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $content = $entity->getContent();
        $basket = $entity->getBasket();
        $human = $entity->getHuman();
        $source = $entity->getSource();

        $this->assertTrue(is_array($content), 'session content missed');
        $this->assertTrue(is_array($basket), 'basket conten not unserialized');
        $this->assertTrue(is_array($human), 'human content not unserialized');
        $this->assertEquals('dldb', $source, 'session source missed');
        $this->assertTrue('kck6Qz' == $human['captcha_text'], 'human not accessible');
        $this->assertTrue($entity->hasRequests(), 'requests not accessible');
        $this->assertTrue($entity->hasProvider(), 'providers not accessible');
        $this->assertTrue($entity->hasScope(), 'scope not accessible');
        $this->assertFalse($entity->hasConfirmationNotification(), 'confirmation notification should not be set');
        $this->assertTrue($entity->hasAuthKey(), 'authKey should be set');
        $this->assertTrue($entity->hasDate(), 'date should be set');
        $this->assertFalse(
            $entity->hasDifferentEntry($entity->getEntryData()),
            'entry data is different to session entry data'
        );

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
        $this->assertTrue($entity->hasStatus(), 'session must have a status');
        $this->assertFalse($entity->isEmpty(), 'session is empty');
        $this->assertFalse($entity->isStalled(), 'session should not be stalled');
        $this->assertFalse($entity->isConfirmed(), 'session should not be confirmed');
        $this->assertFalse($entity->isFinished(), 'session should not be finished');
        $this->assertFalse($entity->isInChange(), 'session should not be in change');
        $this->assertFalse($entity->isProcessDeleted(), 'session should not be reserved');
        $this->assertFalse($entity->hasChangedProcess(), 'status should not be processChanged');
        $this->assertFalse($entity->hasPreviousAppointmentSearch(), 'status should not be inProgress');

        $this->assertFalse($entity->isReserved(), 'session should not be reserved');
        $entity->content['status'] = 'reserved';
        $this->assertTrue($entity->isReserved(), 'session should be reserved');
    }
}
