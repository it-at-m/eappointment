<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\DayOff;

class DayOffTest extends Base
{
    public function testBasic()
    {
        $dayOffList = (new DayOff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas DayOff date 2016-12-25 not recognized.");
        //all dayoff dates of Department 77 Teichstr. 65 (Haus 1), 13407 Berlin.
        $dayOffList = (new DayOff())->readByDepartmentId('77');
        $this->assertEquals('1479250800', $dayOffList->getEntityByName('Personalversammlung')['date']);
    }
}
