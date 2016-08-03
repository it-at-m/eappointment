<?php

namespace BO\Zmsentities\Tests;

abstract class EntityCommonTests extends Base
{

    public function testNew()
    {
        $example = $this->getExample();
        //var_dump($example);
        $this->assertTrue($example->isValid());
    }

    public function getExample()
    {
        $entity = new $this->entityclass();
        $example = $entity::getExample();
        return $example;
    }

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
