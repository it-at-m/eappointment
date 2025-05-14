<?php

namespace BO\Zmsapi\Tests;

class OverallCalendarReadTest extends Base
{
    protected $classname = "OverallCalendarRead";

    private const QUERY = [
        'scopeIds'  => '2001',
        'dateFrom'  => '2025-05-14',
        'dateUntil' => '2025-05-14',
    ];

    public function testCalendarStructure(): void
    {
        $this->setWorkstation();

        $response = $this->get([], self::QUERY);  // <- Änderung hier!
        $json = json_decode((string) $response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('days', $json['data']);
        $this->assertIsArray($json['data']['days']);
    }

    public function testRendering(): void
    {
        $this->setWorkstation();

        $response = $this->get([], self::QUERY);  // <- Änderung hier!
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('"days"', (string)$response->getBody());
    }

    public function testValidationFailure(): void
    {
        $this->setWorkstation();

        $this->expectException(\BO\Mellon\Failure\Exception::class);
        $this->get([], []);  // leerer Request ohne `scopeIds`
    }
}
