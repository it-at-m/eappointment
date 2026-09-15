<?php

namespace BO\Zmsbackend\Tests\Dayoff\Service;

use \BO\Zmsbackend\Dayoff\Service\DayOff;
use \BO\Zmsentities\Dayoff as Entity;
use \BO\Zmsbackend\Helper\CalculateDayOff as Helper;

class DayOffTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $dayOffList = (new Dayoff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas Dayoff date 2016-12-25 not recognized.");
    }

    public function testWriteCommonByYear()
    {
        $dayOffList = (new Dayoff())->readCommonByYear(2016); //all dayoff with departmentid 0
        $dayOffList->addEntity($this->getTestEntity());
        $dayOffList = (new Dayoff())->writeCommonDayoffsByYear($dayOffList, 2016);
        $this->assertEquals(1459461600, $dayOffList->getEntityByName('Test Feiertag')['date']);
    }

    public function testWriteCommonUntilYear()
    {
        $dayOffList = (new Helper(2022, true))->writeDayOffListUntilYear();
        $this->assertEquals(1650146400, $dayOffList->getByDate('2022-04-17')->date);
    }

    public function testDeleteByTimeInterval()
    {
        (new Dayoff())->deleteByTimeInterval(3600);
        $dayOffList = (new Dayoff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertEquals(0, $dayOffList->count());
    }

    protected function getTestEntity()
    {
        return new Entity(array(
          "date" => 1459461600,
          "name" => "Test Feiertag"
        ));
    }

    public function testReadByDepartmentIdReturnsCommonDayoffsOnly()
    {
        $dayOffList = (new Dayoff())->readByDepartmentId('77');
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "Common dayoff date 2016-12-25 not recognized.");
        $this->assertNull($dayOffList->getEntityByName('Personalversammlung'));
    }

    public function testReadByScopeIdReturnsCommonDayoffsOnly()
    {
        $dayOffList = (new Dayoff())->readByScopeId(141);
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'));
        $this->assertNull($dayOffList->getEntityByName('Personalversammlung'));
    }
}
