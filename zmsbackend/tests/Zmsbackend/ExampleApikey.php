<?php

namespace BO\Zmsbackend\Tests;

use BO\Zmsentities\Apikey as Entity;

/**
 * The schema example key is one primary key. Parallel workers each insert it.
 */
final class ExampleApikey
{
    public static function create(string $owner): Entity
    {
        $entity = (new Entity())->createExample();
        $entity->key .= substr(hash('sha256', $owner), 0, 16);

        return $entity;
    }
}
