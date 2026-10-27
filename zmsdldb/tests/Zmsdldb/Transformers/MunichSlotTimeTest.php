<?php

declare(strict_types=1);

namespace BO\Zmsdldb\Tests\Transformers;

use BO\Zmsdldb\Transformers\Munich;
use PHPUnit\Framework\TestCase;

class MunichSlotTimeTest extends TestCase
{
    public function testEveryLocationUsesFiveMinuteSlots(): void
    {
        $result = (new Munich('https://example.test'))->transformLocations(
            [
                'locations' => [[
                    'id' => 10,
                    'altname2' => 'Einbuergerung',
                    'extendedServiceReferences' => [
                        ['refId' => 1],
                        ['refId' => 2],
                    ],
                ]],
            ],
            [
                'data' => [
                    ['id' => 1, 'name' => 'Beratung', 'duration' => 60],
                    ['id' => 2, 'name' => 'Antrag', 'duration' => 90],
                ],
            ]
        );

        $location = $result['locations']['data'][0];

        self::assertSame(5, $location['slotTimeInMinutes']);
        self::assertSame(30, $location['appointmentIntervalInMinutes']);
        self::assertTrue($location['forceSlotTimeUpdate']);
        self::assertSame('12', $location['services'][0]['appointment']['slots']);
        self::assertSame('18', $location['services'][1]['appointment']['slots']);
    }
}
