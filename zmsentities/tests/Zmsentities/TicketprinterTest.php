<?php

namespace BO\Zmsentities\Tests;

class TicketprinterTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Ticketprinter';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertStringContainsString('54', $entity->getHashWith(54), 'Hash not contains organisationId 54');
        error_log(strlen($entity->getHashWith(54)->hash));
        $this->assertTrue(27 == strlen($entity->getHashWith(54)->hash), 'Hash length does not match');
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
        $this->expectException('\BO\Zmsentities\Exception\TicketprinterUnvalidButton');
        $entity = (new $this->entityclass())->getExample();
        $entity->buttonlist = 'test';
        $buttonList = $entity->toStructuredButtonList();
    }

    public function testGetLists()
    {
        $entity = (new $this->entityclass())->getExample();
        $ticketprinter = $entity->toStructuredButtonList();
        $this->assertEquals(1, $ticketprinter->getScopeList()->count());
    }

    public function testGetClusterList()
    {
        $entity = (new $this->entityclass())->getExample();
        $cluster = (new \BO\Zmsentities\Cluster)->getExample();
        $entity['buttons'][] = ['type' => 'cluster', 'cluster'=> $cluster];
        $clusterList = $entity->getClusterList();
        $this->assertEquals(1, $clusterList->count());
    }
}
