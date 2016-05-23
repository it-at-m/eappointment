<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\DayOff;

class DayOffTest extends Base
{
    public function testBasic()
    {
        $dayOffList = (new DayOff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas DayOff date 2016-12-25 not recognized.");

        $dayOffList = (new DayOff())->readByDepartmentId('77'); //all dayoff dates of Department 77 Teichstr. 65 (Haus 1), 13407 Berlin.
        $this->assertEquals('1479247200', $dayOffList->getEntityByName('Personalversammlung')['date']);
    }
}
