<?php

namespace BO\Zmsentities\Tests;

abstract class EntityCommonTests extends Base
{

    public function testNew()
    {
        $entity = new $this->entityclass();
        $example = $entity::getExample();
        //var_dump($example);
        $this->assertTrue($example->isValid());
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
