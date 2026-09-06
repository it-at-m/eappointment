<?php

namespace BO\Zmsentities\Schema;

use Exception;

class Loader
{
    public static function asArray(string $schemaFilename): Schema
    {
        $jsonString = self::asJson($schemaFilename);
        if (!is_string($jsonString)) {
            throw new \BO\Zmsentities\Exception\SchemaMissingJsonFile("Missing JSON-Schema file $schemaFilename");
        }
        $array = json_decode($jsonString, true);
        $object = json_decode($jsonString);
        if (null === $array && $jsonString) {
            $json_error = json_last_error();
            $json_error_list = array(
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
                JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
                JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
                JSON_ERROR_NONE => 'No errors',
            );
            throw new \BO\Zmsentities\Exception\SchemaFailedParseJsonFile(
                "Could not parse JSON File $schemaFilename: " . $json_error_list[$json_error]
            );
        }
        $schema = new Schema($array);
        $schema->setJsonObject($object);
        return $schema;
    }

    /**
     * @return false|string
     */
    public static function asJson(string|false $schemaFilename): string|false
    {
        if ($schemaFilename === false || $schemaFilename === '') {
            throw new \BO\Zmsentities\Exception\SchemaMissingJsonFile("Missing JSON-Schema file");
        }
        $schemaPath = preg_match('#^/#', $schemaFilename) ? false : self::getSchemaPath();
        $directory = $schemaPath !== false ? $schemaPath : '';
        $filename = $directory . DIRECTORY_SEPARATOR . $schemaFilename;
        return file_get_contents($filename);
    }

    /**
     * @return false|string
     */
    public static function getSchemaPath(): string|false
    {
        $pathTrace = [
            __DIR__,
            '..',
            '..',
            '..',
            'schema'
        ];
        return realpath(implode(DIRECTORY_SEPARATOR, $pathTrace));
    }
}
