<?php

namespace BO\Zmsentities\Tests;

class TicketprinterTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Ticketprinter';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertContains('54', $entity->getHashWith(54), 'Hash not contains organisationId 54');
        $this->assertTrue(34 == strlen($entity->getHashWith(54)->hash), 'Hash length does not match');
        $this->assertTrue($entity->isEnabled(), 'Ticketprinter should be enabled');
    }

    public function testStructuredButtonList()
    {
        $entity = (new $this->entityclass())->getExample();
        $ticketprinter = $entity->toStructuredButtonList();
        $this->assertTrue(is_array($ticketprinter->buttons), 'ButtonList is not a valid array');
        $this->assertTrue(
            '/scope/123/' == $ticketprinter->buttons[0]['url'],
            'ButtonList does not contains all possible buttons'
        );
    }

    public function testStructuredButtonListFailed()
    {
        $this->setExpectedException('\BO\Zmsentities\Exception\TicketprinterUnvalidButton');
        $entity = (new $this->entityclass())->getExample();
        $entity->buttonlist = 'test';
        $buttonList = $entity->toStructuredButtonList();
    }

    public function testGetLists()
    {
        $entity = (new $this->entityclass())->getExample();
        $ticketprinter = $entity->toStructuredButtonList();
        $this->assertEquals(1, $ticketprinter->getScopeList()->count());
        $this->assertEquals(1, $ticketprinter->getClusterList()->count());
    }
}
