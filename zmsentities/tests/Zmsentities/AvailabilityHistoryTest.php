<?php

namespace BO\Zmsentities\Tests;

class AvailabilityHistoryTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\AvailabilityHistory';

    public $collectionclass = '\BO\Zmsentities\Collection\AvailabilityHistoryList';

    public function testWeekdayMaskRoundTrip()
    {
        $availability = new \BO\Zmsentities\Availability([
            'weekday' => [
                'monday' => 1,
                'tuesday' => 1,
                'wednesday' => 0,
                'thursday' => 0,
                'friday' => 1,
                'saturday' => 0,
                'sunday' => 0,
            ],
        ]);

        $mask = \BO\Zmsentities\AvailabilityHistory::encodeWeekdayMask($availability);
        $this->assertSame(2 | 4 | 32, $mask);

        $decoded = \BO\Zmsentities\AvailabilityHistory::decodeWeekdayMask($mask);
        $this->assertSame(2, $decoded['monday']);
        $this->assertSame(4, $decoded['tuesday']);
        $this->assertSame(0, $decoded['wednesday']);
        $this->assertSame(32, $decoded['friday']);
        $this->assertSame(0, $decoded['sunday']);
    }
}
