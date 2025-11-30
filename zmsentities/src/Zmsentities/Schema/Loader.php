<?php

namespace BO\Zmsentities\Schema;

use Exception;

class Loader
{
    public static function asArray($schemaFilename)
    {
        // Build cache key from schema filename
        $cacheKey = 'schema_array_' . md5($schemaFilename);
        $schemaPath = self::getSchemaPath();
        $fullPath = ($schemaPath ? $schemaPath . DIRECTORY_SEPARATOR : '') . $schemaFilename;

        // Try to get from cache
        if (class_exists('\App') && isset(\App::$cache) && \App::$cache && file_exists($fullPath)) {
            $cacheMtime = \App::$cache->get($cacheKey . '_mtime');
            if ($cacheMtime !== null) {
                $fileMtime = filemtime($fullPath);
                if ($cacheMtime >= $fileMtime) {
                    $cached = \App::$cache->get($cacheKey);
                    if ($cached !== null) {
                        if (class_exists('\App') && isset(\App::$log) && \App::$log) {
                            \App::$log->info('Schema cache hit', [
                                'schema' => $schemaFilename,
                                'type' => 'array'
                            ]);
                        }
                        return unserialize($cached);
                    }
                }
            }
        }

        // Load and parse schema
        $jsonString = self::asJson($schemaFilename);
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

        // Cache the parsed schema
        if (class_exists('\App') && isset(\App::$cache) && \App::$cache && file_exists($fullPath)) {
            \App::$cache->set($cacheKey, serialize($schema));
            \App::$cache->set($cacheKey . '_mtime', filemtime($fullPath));
            if (class_exists('\App') && isset(\App::$log) && \App::$log) {
                \App::$log->info('Schema cache set', [
                    'schema' => $schemaFilename,
                    'type' => 'array'
                ]);
            }
        }

        return $schema;
    }

    public static function asJson($schemaFilename)
    {
        if (!$schemaFilename) {
            throw new \BO\Zmsentities\Exception\SchemaMissingJsonFile("Missing JSON-Schema file");
        }

        $directory = preg_match('#^/#', $schemaFilename) ? '' : self::getSchemaPath();
        $filename = $directory . DIRECTORY_SEPARATOR . $schemaFilename;

        // Build cache key from schema filename
        $cacheKey = 'schema_json_' . md5($schemaFilename);

        // Try to get from cache
        if (class_exists('\App') && isset(\App::$cache) && \App::$cache && file_exists($filename)) {
            $cacheMtime = \App::$cache->get($cacheKey . '_mtime');
            if ($cacheMtime !== null) {
                $fileMtime = filemtime($filename);
                if ($cacheMtime >= $fileMtime) {
                    $cached = \App::$cache->get($cacheKey);
                    if ($cached !== null) {
                        if (class_exists('\App') && isset(\App::$log) && \App::$log) {
                            \App::$log->info('Schema cache hit', [
                                'schema' => $schemaFilename,
                                'type' => 'json'
                            ]);
                        }
                        return $cached;
                    }
                }
            }
        }

        // Load from disk
        $jsonString = file_get_contents($filename);

        // Cache the JSON string
        if (class_exists('\App') && isset(\App::$cache) && \App::$cache && file_exists($filename)) {
            \App::$cache->set($cacheKey, $jsonString);
            \App::$cache->set($cacheKey . '_mtime', filemtime($filename));
            if (class_exists('\App') && isset(\App::$log) && \App::$log) {
                \App::$log->info('Schema cache set', [
                    'schema' => $schemaFilename,
                    'type' => 'json'
                ]);
            }
        }

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
