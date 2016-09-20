<?php

namespace BO\Zmsentities\Tests;

class ProcessTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Process';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $entity->addRequests('dldb', '122305');
        $this->assertContains('122305', $entity->getRequestCSV(), 'requests are not accessible');

        $entity->addScope('141');
        $this->assertTrue('141' == $entity->getScopeId(), 'scope is not accessible');
        $this->assertTrue('abcd' == $entity->getAuthKey(), 'authKey is not accessible');
        $this->assertTrue('Beispiel Termin' == $entity->getAmendment(), 'amendment is not accessible');

        $entity->setStatus('reserved');
        $this->assertTrue('reserved' == $entity->getStatus(), 'status is not accessible');

        $this->assertTrue(1447931730000 == $entity->getReminderTimestamp(), 'reminder timestamp is not set');

        $this->assertFalse($entity->isConfirmationSmsRequired(), 'Confirmation SMS should not be set to required');
    }

    public function testClient()
    {
        $entity = (new $this->entityclass())->getExample();
        $client = (new \BO\Zmsentities\Client())->getExample();
        $entity->updateClients($client);
        $this->assertFalse($entity->clients[0]['surveyAccepted'], 'client update failed');
        $firstClient = $entity->getFirstClient();
        $this->assertTrue('Max Mustermann' == $firstClient['familyName'], 'first client not found');
    }

    public function testAppointment()
    {
        $entity = (new $this->entityclass())->getExample();
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $entity->addAppointment($appointment);
        $this->assertTrue($entity->hasAppointment(1447869172, 123), 'appointment is not accessible');
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');
        $firstAppointment = $entity->getFirstAppointment();
    }
}
