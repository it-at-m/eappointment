<?php

namespace BO\Zmsentities\Tests;

abstract class EntityCommonTests extends Base
{

    public function testNew()
    {
        $example = $this->getExample();
        //var_dump($example);
        $this->assertTrue($example->testValid());
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
        $entity->testValid();
    }

    public function assertEntityList($entityClass, $entityList)
    {
        foreach ($entityList as $entity) {
            $this->assertEntity($entityClass, $entity);
        }
    }

    public function testLessData()
    {
        $example = $this->getExample()->withLessData();
        $this->assertTrue($example->testValid());
    }
}
