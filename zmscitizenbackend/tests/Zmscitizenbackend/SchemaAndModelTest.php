<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests;

use BO\Zmscitizenbackend\Exception\SchemaMissingJsonFile;
use BO\Zmscitizenbackend\Exception\SchemaValidation;
use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Office;
use BO\Zmscitizenbackend\Models\ThinnedScope;
use BO\Zmscitizenbackend\Schema\Loader;
use PHPUnit\Framework\TestCase;

class SchemaAndModelTest extends TestCase
{
    public function testSchemaPathPointsAtCitizenBackend(): void
    {
        $path = Loader::getSchemaPath();
        $this->assertStringEndsWith('/zmscitizenbackend/schema', $path);
        $this->assertFileExists($path . '/citizenapi/office.json');
    }

    public function testLoaderReadsOfficeSchema(): void
    {
        $schema = Loader::asArray('citizenapi/office.json');
        $this->assertSame('Office', $schema['title']);
        $this->assertContains('id', $schema['required']);
    }

    public function testLoaderResolvesRelativeRefs(): void
    {
        $schema = Loader::asArray('citizenapi/office.json')->withResolvedReferences(4);
        $scope = $schema['properties']['scope'];
        $this->assertIsArray($scope);
        $this->assertArrayHasKey('properties', $scope);
        $this->assertArrayHasKey('provider', $scope['properties']);
        $this->assertArrayNotHasKey('$ref', $scope);
    }

    public function testMissingSchemaFileThrows(): void
    {
        $this->expectException(SchemaMissingJsonFile::class);
        Loader::asArray('citizenapi/does-not-exist.json');
    }

    public function testOfficeModelValidatesAgainstOwnedSchema(): void
    {
        $office = new Office(1, 'Bürgerbüro');
        $this->assertSame(1, $office->id);
        $this->assertSame('Bürgerbüro', $office->toArray()['name']);
    }

    public function testThinnedScopeRejectsInvalidEmail(): void
    {
        $this->expectException(SchemaValidation::class);
        new ThinnedScope(id: 1, emailFrom: 'not-an-email');
    }

    public function testOfficeListHydratesFromOwnedCollectionSchema(): void
    {
        $list = new OfficeList([new Office(10, 'Ruppertstraße')]);
        $payload = $list->toArray();
        $this->assertCount(1, $payload['offices']);
        $this->assertSame(10, $payload['offices'][0]['id']);
    }
}
