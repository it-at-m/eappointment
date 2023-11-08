<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Session as Query;
use \BO\Zmsentities\Session as Entity;

class SessionTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->updateEntity($input);
        $entity = $query->readEntity($entity->name, $entity->id);
        $this->assertEquals('7b89b1c1fd6c7a52fa748ff663babd0c', $entity->id);
        $deleteTest = $query->deleteEntity($entity->name, $entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Session from Database.");
    }

    public function testDeleteByTime()
    {
        $query = new Query();
        $entity = $this->getTestEntity();
        $entity = $query->updateEntity($entity);
        sleep(3);
        $query->deleteByTimeInterval($entity->name, 2);
        $entity = $query->readEntity($entity->name, $entity->id);
        $this->assertFalse(isset($entity->id), "Failed to delete Session from Database.");
    }

    protected function getTestEntity()
    {
        $entity = Entity::createExample();
        return $entity;
    }
}
