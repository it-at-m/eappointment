<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Schema;

use BO\Zmscitizenbackend\Schema\Exception\SchemaFailedParseJsonFile;
use BO\Zmscitizenbackend\Schema\Exception\SchemaMissingJsonFile;
use BO\Zmscitizenbackend\Office\Model\Office;
use BO\Zmscitizenbackend\Schema\Loader;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    public function testSchemaPathIsOwnedByZmscitizenbackend(): void
    {
        $schemaPath = Loader::getSchemaPath();
        $expected = realpath(dirname(__DIR__, 3) . '/schema');

        $this->assertNotFalse($schemaPath);
        $this->assertSame($expected, $schemaPath);
        $this->assertFileExists($schemaPath . '/citizenapi/office.json');
        $this->assertStringContainsString('/zmscitizenbackend/schema', $schemaPath);
        $this->assertStringNotContainsString('/zmsentities/schema', $schemaPath);
    }

    public function testAsArrayLoadsCitizenSchemaFromLocalPath(): void
    {
        $schema = Loader::asArray('citizenapi/office.json');
        $this->assertSame('Office', $schema['title']);

        $fromDisk = json_decode(
            file_get_contents(Loader::getSchemaPath() . '/citizenapi/office.json'),
            true
        );
        $this->assertSame($fromDisk['title'], $schema['title']);
        $this->assertSame($fromDisk['properties']['id']['type'], $schema['properties']['id']['type']);
    }

    public function testOfficeModelValidatesAgainstLocalSchema(): void
    {
        $office = new Office(1, 'Test office');
        $this->assertTrue($office->testValid());
        $this->assertSame(
            Loader::asArray(Office::$schema)['title'],
            'Office'
        );
    }

    public function testMissingSchemaFileThrows(): void
    {
        $this->expectException(SchemaMissingJsonFile::class);
        Loader::asJson('');
    }

    public function testInvalidJsonThrows(): void
    {
        $empty = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($empty, '{');
        try {
            $this->expectException(SchemaFailedParseJsonFile::class);
            Loader::asArray($empty);
        } finally {
            unlink($empty);
        }
    }
}
