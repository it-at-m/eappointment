<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
 
namespace BO\Dldb\Tests;

use BO\Dldb\FileAccess;

class FileTest extends \PHPUnit_Framework_TestCase
{

    public function testIds()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $location = $access->fetchLocation(122281);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fetchService(120703);
        $this->assertNotFalse($service);
        $locationList = $access->fetchLocationList(120703);
        $this->assertArrayHasKey(122281, $locationList);
        $serviceList = $access->fetchServiceList(122281);
        $this->assertArrayHasKey(120703, $serviceList);
        $authorityList = $access->fetchAuthorityList([120703]);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fetchServiceFromCsv(120703);
        $this->assertArrayHasKey(120703, $serviceList);
        $locationList = $access->fetchLocationFromCsv(122281);
        $this->assertArrayHasKey(122281, $locationList);
    }

    public function testFail()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $location = $access->fetchLocation(1);
        $this->assertFalse($location);
        $service = $access->fetchService(1);
        $this->assertFalse($service);
    }

    public function testFailLocation()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        new FileAccess('dummy', 'dummy');
    }

    public function testFailService()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        new FileAccess(LOCATION_JSON, 'dummy');
    }

    public function testFailJsonLocation()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        new FileAccess(__FILE__, 'dummy');
    }

    public function testFailJsonService()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        new FileAccess(LOCATION_JSON, __FILE__);
    }
}
