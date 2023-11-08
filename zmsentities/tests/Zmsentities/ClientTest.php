<?php

namespace BO\Zmsentities\Tests;

class ClientTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Client';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->hasEmail(), 'client should have an email');
        $this->assertTrue($entity->hasTelephone(), 'client should have a telephone number');
        $this->assertFalse($entity->hasSurveyAccepted(), 'client should have survey not accepted');
        $this->assertTrue(0 == $entity->getEmailSendCount(), 'client emailSendCount should be 0');
        $this->assertTrue(0 == $entity->getNotificationsSendCount(), 'client notificationsSendCount should be 0');

        $entity = $entity->withResolveLevel(0);
        $this->assertEquals(0, $entity->getResolveLevel(), 'client has unexpected references');
    }
}
