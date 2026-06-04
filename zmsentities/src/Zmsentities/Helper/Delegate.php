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
            $reference = $entity;
            $lastProperty = array_pop($propertyPath);
            foreach ($propertyPath as $property) {
                $reference =& $reference[$property];
            }
            $reference[$lastProperty] = $newValue;

            return $entity;
        };
    }
}
