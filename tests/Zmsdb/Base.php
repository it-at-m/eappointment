<?php

namespace BO\Zmsdb\Tests;

abstract class Base extends \PHPUnit_Framework_TestCase
{

    public function assertEntity($entityClass, $entity)
    {
        $this->assertInstanceOf($entityClass, $entity);
        $this->assertTrue($entity->isValid());
    }

    public function assertEntityList($entityClass, $entityList)
    {
        foreach ($entityList as $entity) {
            $this->assertEntity($entityClass, $entity);
        }
    }
}
