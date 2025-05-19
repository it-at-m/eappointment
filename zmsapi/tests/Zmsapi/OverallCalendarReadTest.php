<?php

namespace BO\Zmsapi\Tests;

use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Errors\ErrorFormatter;

class OverallCalendarReadTest extends Base
{
    protected $classname = "OverallCalendarRead";

    private const VALID_PARAMS = [
        'scopeIds'  => '2001',
        'dateFrom'  => '2025-05-14',
        'dateUntil' => '2025-05-14',
    ];

    public function testCalendarStructure(): void
    {
        $this->setWorkstation();

        $response = $this->render([], self::VALID_PARAMS);
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

        $response = $this->render([], self::VALID_PARAMS);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('"days"', (string)$response->getBody());
    }

    public function testValidationFailure(): void
    {
        $this->setWorkstation();

        $this->expectException(\BO\Mellon\Failure\Exception::class);

        $this->render([], ['scopeIds' => '', 'dateFrom' => '2025-05-14', 'dateUntil' => '2025-05-14']);
    }

    public function testResponseMatchesSchema(): void
    {
        $this->setWorkstation();

        $response = $this->render([], self::VALID_PARAMS);
        $json = json_decode((string) $response->getBody());

        // Lade das JSON-Schema von einer Datei
        $schemaPath = __DIR__ . '/fixtures/calendar.json';
        $schemaData = json_decode(file_get_contents($schemaPath));

        $validator = new Validator();

        // Validierung durchführen
        $result = $validator->schemaValidation($json, $schemaData);

        if (!$result->isValid()) {
            $formatter = new ErrorFormatter();
            $errors = $formatter->format($result->error());
            error_log("Schema validation failed:\n" . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        }

        $this->assertTrue($result->isValid(), 'Response does not match calendar schema');
    }
}
