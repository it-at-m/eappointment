<?php
namespace BO\Zmsapi\Tests;

use BO\Zmsdb\Connection\Select;


class OverallCalendarReadTest extends Base
{
    protected $classname = 'OverallCalendarRead';

    public function testCalendarStructure()
    {
        $response = $this->render(
            [],
            [],
            [
                'scopeIds'   => '2001',
                'dateFrom'   => '2025-05-14',
                'dateUntil'  => '2025-05-14',
            ]
        );

        $json = json_decode((string)$response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($json['meta']['error']);

        $day = $json['data']['days'][0];
        $this->assertEquals(strtotime('2025‑05‑14'), $day['date']);

        $scope = $day['scopes'][0];
        $this->assertEquals(2001,   $scope['id']);
        $this->assertEquals(3,      $scope['maxSeats']);

        $time0900 = $scope['times'][0];
        $this->assertEquals('09:00', $time0900['name']);

        $this->assertCount(3, $time0900['seats']);

        $this->assertSame('termin', $time0900['seats'][0]['status']);
        $this->assertSame(555001,   $time0900['seats'][0]['processId']);
        $this->assertSame(2,        $time0900['seats'][0]['slots']);

        $this->assertSame('open',   $time0900['seats'][1]['status']);

        $this->assertSame('open',   $time0900['seats'][2]['status']);

        $time0905 = $scope['times'][1];
        $this->assertEquals('skip', $time0905['seats'][0]['status']);
    }
}
