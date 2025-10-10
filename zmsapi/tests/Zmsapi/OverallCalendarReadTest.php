<?php

namespace BO\Zmsapi\Tests;

use BO\Mellon\Failure\Exception;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\SchemaLoader;

class OverallCalendarReadTest extends Base
{
    protected $classname = "OverallCalendarRead";

    private const VALID_PARAMS = [
        'scopeIds'  => '65202',
        'dateFrom'  => '2025-05-14',
        'dateUntil' => '2025-05-14',
    ];

    protected function initializeSuperUserWorkstation(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setRights('superuser');
    }

    public function testCalendarStructure(): void
    {
        $this->initializeSuperUserWorkstation();

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
        $this->initializeSuperUserWorkstation();

        $response = $this->render([], self::VALID_PARAMS);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('"days"', (string)$response->getBody());
    }

    public function testValidationFailure(): void
    {
        $this->initializeSuperUserWorkstation();

        $this->expectException(Exception::class);

        $this->render([], ['scopeIds' => '', 'dateFrom' => '2025-05-14', 'dateUntil' => '2025-05-14']);
    }

    public function testResponseMatchesSchema(): void
    {
        $this->initializeSuperUserWorkstation();

        $response = $this->render([], self::VALID_PARAMS);
        $json     = json_decode((string) $response->getBody());
        $this->assertNotNull($json, 'Response is not valid JSON');

        $schemaPath    = __DIR__ . '/fixtures/calendar.json';
        $schemaRaw     = file_get_contents($schemaPath);
        $this->assertNotFalse($schemaRaw, "Schema file could not be read: $schemaPath");

        $schemaDecoded = json_decode($schemaRaw);
        $this->assertNotNull($schemaDecoded, "Schema file is not valid JSON");

        $loader = new SchemaLoader();
        $schema = $loader->loadObjectSchema($schemaDecoded);

        $validator = new Validator();
        $result    = $validator->validate($json, $schema);

        $this->assertNotNull($result, "Schema validation result is null");
        if (!$result->isValid()) {
            $formatter = new ErrorFormatter();
            $errors    = $formatter->format($result->error());
            error_log(
                "Schema validation failed:\n"
                . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
        $this->assertTrue($result->isValid(), 'Response does not match calendar schema');
    }

    public function testDeltaBranchIncludesCancelledAndHasTombstonesField(): void
    {
        $this->initializeSuperUserWorkstation();

        $params = [
            'scopeIds'    => '65202',
            'dateFrom'    => '2025-05-14',
            'dateUntil'   => '2025-05-14',
            'updateAfter' => '2000-01-01 00:00:00',
        ];
        $response = $this->render([], $params);
        $json = json_decode((string)$response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($json['data']['delta']);
        $this->assertArrayHasKey('tombstones', $json['data']);
        $this->assertIsArray($json['data']['tombstones']);

        $this->assertNotEmpty($json['data']['days']);
        $day = $json['data']['days'][0];
        $this->assertArrayHasKey('scopes', $day);
        $this->assertIsArray($day['scopes']);

        $anyEvents = array_merge(...array_map(fn($s) => $s['events'] ?? [], $day['scopes']));
        $this->assertIsArray($anyEvents);
    }
}
