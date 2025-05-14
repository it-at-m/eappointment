<?php

namespace BO\Zmsapi\Tests;

class OverallCalendarReadTest extends Base
{
    protected $classname = "OverallCalendarRead";

    public function testCalendarStructure(): void
    {
        $this->setWorkstation();

        $response = $this->render([
            'scopeIds'  => '2001',
            'dateFrom'  => '2025-05-14',
            'dateUntil' => '2025-05-14'
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('days', $json['data']);
        $this->assertIsArray($json['data']['days']);
    }

    public function testRendering(): void
    {
        $this->setWorkstation();

        $response = $this->render([
            'scopeIds'  => '2001',
            'dateFrom'  => '2025-05-14',
            'dateUntil' => '2025-05-14'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('"days"', (string)$response->getBody());
    }

    public function testValidationFailure(): void
    {
        $this->setWorkstation();

        $this->expectException(\BO\Mellon\Failure\Exception::class);
        $this->render([]);
    }
}
