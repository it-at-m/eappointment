<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\DayOff;

class DayOffTest extends Base
{
    public function testBasic()
    {
        $dayOffList = (new DayOff())->readByYear('2016'); //all dayoff dates in 2016
        $this->assertTrue($dayOffList->hasEntityByDate('2016-12-25'), "XMas DayOff date 2016-12-25 not recognized.");
    }


}
