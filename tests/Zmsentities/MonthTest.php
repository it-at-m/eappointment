<?php

namespace BO\Zmsentities\Tests;

class MonthTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-11-31';

    public $entityclass = '\BO\Zmsentities\Month';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        foreach ($entity->days as $day) {
            $day = new \BO\Zmsentities\Day($day);
            $day->getWithStatus();
            if ($day->isBookable()) {
                $this->assertTrue(
                    $day->year .'-'. $day->month .'-'. $day->day == self::FIRST_DAY,
                    'Bookable day '. self::FIRST_DAY. ' exptected'
                    );
            }
            $this->assertInstanceOf('BO\Zmsentities\Day', $day);
        }
    }
}
