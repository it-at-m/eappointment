<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use BO\Zmscitizenbackend\Exception\SchemaFailedParseJsonFile;
use BO\Zmscitizenbackend\Exception\SchemaMissingJsonFile;

class Loader
{
    public static function asArray(string $schemaFilename): Schema
    {
        $path = self::resolvePath($schemaFilename);
        $jsonString = file_get_contents($path);
        if ($jsonString === false) {
            throw new SchemaMissingJsonFile("Could not read JSON-Schema file $path");
        }
        $array = json_decode($jsonString, true);
        if (null === $array && $jsonString !== '' && $jsonString !== 'null') {
            throw new SchemaFailedParseJsonFile("Could not parse JSON File $path");
        }
        $schema = new Schema(is_array($array) ? $array : []);
        $schema->setSourcePath($path);
        return $schema;
    }

    public static function resolvePath(string $schemaFilename): string
    {
        if ($schemaFilename === '') {
            throw new SchemaMissingJsonFile("Missing JSON-Schema file");
        }
        if ($schemaFilename[0] === '/') {
            $filename = $schemaFilename;
        } else {
            $filename = self::getSchemaPath() . DIRECTORY_SEPARATOR . $schemaFilename;
        }
        $real = realpath($filename);
        if ($real === false) {
            throw new SchemaMissingJsonFile("Missing JSON-Schema file: $schemaFilename");
        }
        return $real;
    }

    public static function getSchemaPath(): string
    {
        $pathTrace = [
            __DIR__,
            '..',
            '..',
            '..',
            'schema'
        ];
        $path = realpath(implode(DIRECTORY_SEPARATOR, $pathTrace));
        if ($path === false) {
            throw new SchemaMissingJsonFile("Could not resolve Citizen schema directory");
        }
        return $path;
    }
}
