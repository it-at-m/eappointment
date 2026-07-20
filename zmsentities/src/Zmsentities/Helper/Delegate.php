<?php

namespace BO\Zmsentities\Helper;

use BO\Zmsentities\Schema\Entity;

class Delegate
{
    protected $entity;

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function setter(...$propertyPath): callable
    {
        $entity = $this->getEntity();

        return function ($newValue) use ($propertyPath, $entity): Entity {
            self::setValueAtPath($entity, $propertyPath, $newValue);

            return $entity;
        };
    }

    private static function setValueAtPath(&$container, array $propertyPath, mixed $newValue): void
    {
        $property = array_shift($propertyPath);
        if ($propertyPath === []) {
            $container[$property] = $newValue;

            return;
        }

        self::setValueAtPath($container[$property], $propertyPath, $newValue);
    }
}
