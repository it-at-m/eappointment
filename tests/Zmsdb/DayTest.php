<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Day;
use \BO\Zmsentities\Day as Entity;

class DayTest extends Base
{
    public function testReadDayListByMonth()
    {
        $scopeList =  new \BO\Zmsentities\Collection\ScopeList();
        $scopeList->addEntity(new \BO\Zmsentities\Scope(['id' => 141]));
        //$dayOffList = (new Day())->readDayListByMonth($scopeList, 2016, 5);
        //var_dump($dayOffList);
    }
}
