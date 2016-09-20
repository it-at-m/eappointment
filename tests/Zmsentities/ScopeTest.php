<?php

namespace BO\Zmsentities\Tests;

class ScopeTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Scope';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(is_array($entity->getNotificationPreferences()), 'Notification preferences not available');
        $this->assertContains('erfolgreich', $entity->getConfirmationContent(), 'Confirmation content not available');
        $this->assertContains('Warteraum', $entity->getHeadsUpContent(), 'Confirmation HeadsUpContent not available');
        $this->assertTrue('23' == $entity->getStatus('queue','givenNumberCount'), 'Status is not accessible');
        $this->assertTrue(null === $entity->getContactEmail(), 'Contact eMail should not be available');
        $this->assertContains('Flughafen', $entity->getName(), 'Contact name not available');
        $this->assertEquals('dritte Tür rechts', $entity->getScopeInfo(), 'Scope info (from hint) is not available');
        $this->assertEquals(null, $entity->getScopeHint(), 'Scope hint (from hint) is not available');
    }

    public function testProvider()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue('123456' == $entity->getProviderId(), 'ProviderId does not exists');
        $entity->provider = array('$ref' => '/provider/123456/');
        $this->assertTrue('123456' == $entity->getProviderId(), 'ProviderId does not exists');
        $entity->provider = null;
        try {
            $entity->getProviderId();
        } catch (\BO\Zmsentities\Exception\ScopeMissingProvider $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testGetPreferences()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue('3' == $entity->getPreference('queue', 'callCountMax'), 'string preference not available');
        $this->assertTrue('1' == $entity->getPreference('survey', 'enabled', true), 'bool preference not available');
    }
}
