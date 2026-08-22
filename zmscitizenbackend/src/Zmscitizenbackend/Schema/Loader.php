<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use BO\Zmscitizenbackend\Exceptions\SchemaFailedParseJsonFile;
use BO\Zmscitizenbackend\Exceptions\SchemaMissingJsonFile;

class Loader
{
    public static function asArray(string $schemaFilename): Schema
    {
        $jsonString = self::asJson($schemaFilename);
        $array = json_decode($jsonString, true);
        $object = json_decode($jsonString);
        if (null === $array && $jsonString) {
            $jsonError = json_last_error();
            $jsonErrorList = [
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
                JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
                JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
                JSON_ERROR_NONE => 'No errors',
            ];
            throw new SchemaFailedParseJsonFile(
                "Could not parse JSON File $schemaFilename: " . $jsonErrorList[$jsonError]
            );
        }
        $schema = new Schema($array);
        $schema->setJsonObject($object);
        return $schema;
    }

    public static function asJson(string $schemaFilename): string|false
    {
        if (!$schemaFilename) {
            throw new SchemaMissingJsonFile("Missing JSON-Schema file");
        }
        $directory = preg_match('#^/#', $schemaFilename) ? '' : self::getSchemaPath();
        $filename = $directory . DIRECTORY_SEPARATOR . $schemaFilename;
        return file_get_contents($filename);
    }

    public static function getSchemaPath(): string|false
    {
        $pathTrace = [
            __DIR__,
            '..',
            '..',
            '..',
            'schema',
        ];
        return realpath(implode(DIRECTORY_SEPARATOR, $pathTrace));
    }
}
