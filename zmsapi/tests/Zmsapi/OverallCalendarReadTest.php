<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\OverallCalendarRead;
use BO\Zmsdb\Tests\Base;

class OverallCalendarReadTest extends Base
{
    private const ENDPOINT = OverallCalendarRead::class;
    private const PARAMS = [
        'scopeIds'  => '2001',
        'dateFrom'  => '2025-05-14',
        'dateUntil' => '2025-05-14',
    ];

    public function testCalendarStructure(): void
    {
        $response = $this->render(self::PARAMS, [], [], self::ENDPOINT);
        $json = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('days', $json['data']);
        $this->assertIsArray($json['data']['days']);
        $this->assertGreaterThanOrEqual(1, count($json['data']['days']));
    }

    public function testRendering(): void
    {
        $response = $this->render(self::PARAMS, [], [], self::ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('"days"', $body);
    }
}
