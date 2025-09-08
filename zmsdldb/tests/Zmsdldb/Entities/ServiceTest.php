<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Tests\Entities;

use BO\Zmsdldb\FileAccess;

class ServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testFilter()
    {
        $access = new FileAccess();
        $access->loadFromPath(FIXTURES);
        $serviceList = $access->fromService()->fetchList();
        $this->assertTrue($serviceList instanceof \BO\Zmsdldb\Collection\Services);
        $this->assertTrue($serviceList[SERVICE_SINGLE]->hasAppointments());
        $this->assertTrue($serviceList[SERVICE_SINGLE]->hasAppointments(true));
        // Aufenthaltsgenehmigung Praktikum
        $this->assertFalse($serviceList[305303]->hasAppointments());
        $this->assertTrue($serviceList[305303]->hasAppointments(true));
    }
}
