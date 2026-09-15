<?php

namespace BO\Zmsentities;

/**
 * Schema-backed entity (ArrayObject::ARRAY_AS_PROPS); document dynamic keys for Psalm.
 *
 * @property bool $error
 * @property string $message
 * @property string|null $exception
 * @property string $generated
 * @property string $server
 */
class Metaresult extends Schema\Entity
{
    public static $schema = "metaresult.json";
}
