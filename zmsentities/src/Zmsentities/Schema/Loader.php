<?php

namespace BO\Zmsentities\Schema;

use Exception;

class Loader
{
    private static function isCacheAvailable(): bool
    {
        return class_exists('\App') && isset(\App::$cache) && \App::$cache;
    }

    private static function validateCacheMtime(string $cacheKey, string $fullPath): ?int
    {
        $cacheMtime = \App::$cache->get($cacheKey . '_mtime');
        if ($cacheMtime === null) {
            return null;
        }

        // Validate file exists and compare modification times
        if (!file_exists($fullPath)) {
            return null;
        }

        $fileMtime = filemtime($fullPath);
        if ($fileMtime === false) {
            return null;
        }
        if ($cacheMtime < $fileMtime) {
            return null;
        }

        return $cacheMtime;
    }

    private static function logCacheHit(string $schemaFilename, string $type): void
    {
        if (class_exists('\App') && isset(\App::$log) && \App::$log) {
            \App::$log->debug('Schema cache hit', [
                'schema' => $schemaFilename,
                'type' => $type
            ]);
        }
    }

    private static function getCachedSchema(string $cacheKey, string $fullPath, string $schemaFilename, string $type)
    {
        if (!self::isCacheAvailable()) {
            return null;
        }

        if (self::validateCacheMtime($cacheKey, $fullPath) === null) {
            return null;
        }

        $cached = \App::$cache->get($cacheKey);
        if ($cached === null) {
            return null;
        }

        self::logCacheHit($schemaFilename, $type);
        return $cached;
    }

    private static function setCachedSchema(string $cacheKey, string $fullPath, string $schemaFilename, string $type, $data): void
    {
        if (!class_exists('\App') || !isset(\App::$cache) || !\App::$cache || !file_exists($fullPath)) {
            return;
        }

        \App::$cache->set($cacheKey, $data);
        $fileMtime = filemtime($fullPath);
        if ($fileMtime !== false) {
            \App::$cache->set($cacheKey . '_mtime', $fileMtime);
        }
        if (class_exists('\App') && isset(\App::$log) && \App::$log) {
            \App::$log->debug('Schema cache set', [
                'schema' => $schemaFilename,
                'type' => $type
            ]);
        }
    }

    private static function parseJsonSchema(string $jsonString, string $schemaFilename): array
    {
        $array = json_decode($jsonString, true);
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
        return $array;
    }

    public static function asArray($schemaFilename)
    {
        // Build cache key from schema filename
        $cacheKey = 'schema_array_' . md5($schemaFilename);

        // Handle absolute paths consistently with asJson()
        if (preg_match('#^/#', $schemaFilename)) {
            $fullPath = $schemaFilename;
        } else {
            $schemaPath = self::getSchemaPath();
            $fullPath = $schemaPath . DIRECTORY_SEPARATOR . $schemaFilename;
        }

        // Try to get from cache
        $cached = self::getCachedSchema($cacheKey, $fullPath, $schemaFilename, 'array');
        if ($cached !== null) {
            // Reconstruct Schema from cached JSON string (safer than unserialize)
            $cachedArray = json_decode($cached, true);
            $cachedObject = json_decode($cached);
            $schema = new Schema($cachedArray);
            $schema->setJsonObject($cachedObject);
            return $schema;
        }

        // Load and parse schema
        $jsonString = self::asJson($schemaFilename);
        $array = self::parseJsonSchema($jsonString, $schemaFilename);
        $object = json_decode($jsonString);
        $schema = new Schema($array);
        $schema->setJsonObject($object);

        // Cache the JSON string instead of serialized object (safer than serialize)
        self::setCachedSchema($cacheKey, $fullPath, $schemaFilename, 'array', $jsonString);

        return $schema;
    }

    public static function asJson($schemaFilename)
    {
        if (!$schemaFilename) {
            throw new \BO\Zmsentities\Exception\SchemaMissingJsonFile("Missing JSON-Schema file");
        }

        // Handle absolute paths - avoid double separator
        if (preg_match('#^/#', $schemaFilename)) {
            $filename = $schemaFilename;
        } else {
            $directory = self::getSchemaPath();
            $filename = $directory . DIRECTORY_SEPARATOR . $schemaFilename;
        }

        // Build cache key from schema filename
        $cacheKey = 'schema_json_' . md5($schemaFilename);

        // Try to get from cache
        $cached = self::getCachedSchema($cacheKey, $filename, $schemaFilename, 'json');
        if ($cached !== null) {
            return $cached;
        }

        // Load from disk
        $jsonString = file_get_contents($filename);
        if ($jsonString === false) {
            throw new \BO\Zmsentities\Exception\SchemaMissingJsonFile(
                "Could not read JSON-Schema file: $filename"
            );
        }

        // Cache the JSON string
        self::setCachedSchema($cacheKey, $filename, $schemaFilename, 'json', $jsonString);

        return $jsonString;
    }

    public static function getSchemaPath()
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
