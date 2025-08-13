<?php
namespace BO\Zmsapi\Tests;

use BO\Mellon\Failure\Exception;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Errors\ErrorFormatter;

class RequestVariantListTest extends Base
{
    protected $classname = "RequestVariantList";

    private function initializeSuperUserWorkstation(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setRights('superuser');
    }

    public function testRenderingOk(): void
    {
        $this->initializeSuperUserWorkstation();

        $response = $this->render();
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $this->assertNotSame('', $response->getHeaderLine('Last-Modified'));
    }

    public function testListIsSortedAndTyped(): void
    {
        $this->initializeSuperUserWorkstation();

        $response = $this->render();
        $json = json_decode((string)$response->getBody(), true);

        $expected = [
            ['id' => 1, 'name' => 'A – Abmeldung'],
            ['id' => 2, 'name' => 'B – Anmeldung'],
            ['id' => 3, 'name' => 'C – Änderungsmeldung'],
        ];
        $this->assertSame($expected, $json['data']);

        foreach ($json['data'] as $row) {
            $this->assertIsInt($row['id']);
            $this->assertIsString($row['name']);
        }
    }

    public function testResponseMeta(): void
    {
        $this->initializeSuperUserWorkstation();

        $response = $this->render();
        $json = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('meta', $json);
        $this->assertArrayHasKey('server', $json['meta']);
        $this->assertArrayHasKey('generated', $json['meta']);
        $this->assertArrayHasKey('error', $json['meta']);
        $this->assertFalse($json['meta']['error']);
    }

    public function testResponseMatchesSchema(): void
    {
        $this->initializeSuperUserWorkstation();

        $response = $this->render();
        $payload  = json_decode((string)$response->getBody());
        $this->assertNotNull($payload, 'Response ist kein valides JSON');

        $schemaPath = __DIR__ . '/fixtures/requestvariant_list.schema.json';
        $raw = file_get_contents($schemaPath);
        $this->assertNotFalse($raw, "Schema-Datei nicht lesbar: $schemaPath");

        $decoded = json_decode($raw);
        $this->assertNotNull($decoded, "Schema-Datei ist kein valides JSON");

        $loader = new SchemaLoader();
        $schema = $loader->loadObjectSchema($decoded);

        $validator = new Validator();
        $result    = $validator->validate($payload, $schema);

        if (!$result->isValid()) {
            $formatter = new ErrorFormatter();
            $errors    = $formatter->format($result->error());
            error_log("Schema validation failed:\n" . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        $this->assertTrue($result->isValid(), 'Response entspricht nicht dem RequestVariant-List-Schema');
    }
}
