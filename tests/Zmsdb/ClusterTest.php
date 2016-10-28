<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Cluster as Query;
use \BO\Zmsentities\Cluster as Entity;

class ClusterTest extends Base
{
    public function testReadList()
    {
        $query = new Query();
        $entityList = $query->readList(1);
        $this->assertEntityList("\\BO\\Zmsentities\\Cluster", $entityList);
    }

    public function testReadListByDepartment()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entityList = $query->readByDepartmentId(72, 1); //by Egon-Erwin-Kisch-Str.
        $this->assertEntityList("\\BO\\Zmsentities\\Cluster", $entityList);
        $this->assertEquals(true, $entityList->hasScope('134')); //Bürgeramt 1 (Neu- Hohenschönhausen) Egon-Erwin-Kisch-Straße exists
        $this->assertEquals(true, $entityList->hasScope('135')); //Bürgeramt 2 (Lichtenberg) Normannenstr. exists
    }
    
    public function testReadIsOpenedScopeList()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $entityList = $query->readIsOpenedScopeList(60, $now); //by Egon-Erwin-Kisch-Str. Cluster
        $this->assertEquals(true, 0 <= count($entityList));
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->writeEntity($input);
        $this->assertEquals('Bürger- und Standesamt', $entity->getName());

        $entity->name = 'Bürger- und Standesamt Test';
        $entity = $query->updateEntity($entity->id, $entity);
        $this->assertEquals('Bürger- und Standesamt Test', $entity->getName());

        $entityList = $query->readList(1);
        $this->assertEquals(true, $entityList->hasEntity($entity->id)); //last inserted

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Cluster from Database.");
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->getExample();
    }
}
