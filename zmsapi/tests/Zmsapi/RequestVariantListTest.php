<?php
namespace BO\Zmsapi\Tests;

use BO\Mellon\Failure\Exception;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Errors\ErrorFormatter;

class RequestVariantListTest extends Base
{
    protected $classname = "RequestVariantList";

    public function testRenderingOk(): void
    {
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
        $response = $this->render();
        $payload  = json_decode((string)$response->getBody());
        $this->assertNotNull($payload, 'Response ist kein valides JSON');

        $schemaPath = __DIR__ . '/fixtures/requestvariant_list.json';
        $this->assertFileExists($schemaPath, "Schema-Datei fehlt: $schemaPath");
        $this->assertTrue(is_readable($schemaPath), "Schema-Datei nicht lesbar: $schemaPath");
        $raw = file_get_contents($schemaPath);

        $decoded = json_decode($raw);
        $this->assertNotNull($decoded, "Schema-Datei ist kein valides JSON");

        $loader = new SchemaLoader();
        $schema = $loader->loadObjectSchema($decoded);

        $validator = new Validator();
        $result    = $validator->validate($payload, $schema);

        if (!$result->isValid()) {
        $formatter = new ErrorFormatter();
        $errors    = $formatter->format($result->error());
        $this->fail("Response entspricht nicht dem RequestVariant-List-Schema:\n" . json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
