<?php

namespace BO\Zmsentities\Tests;

class CalldisplayTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Calldisplay';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $resolvedEntity = $entity->withResolvedCollections($this->getTestInput());
        $resolvedEntity['scopes'][] = $this->getTestScope();
        $resolvedEntity['clusters'][] = $this->getTestCluster();

        $resolvedEntity->getClusterList()[0]['scopes'][] = $resolvedEntity->getScopeList()->getFirst();
        $resolvedEntity = $resolvedEntity->withOutClusterDuplicates();
        $this->assertEquals(2, $resolvedEntity->getScopeList()->count());
        $this->assertEquals(2, $resolvedEntity->getClusterList()->count());

        $serverTime = (new \DateTimeImmutable())->getTimestamp();
        $resolvedEntity->setServerTime($serverTime);
        $this->assertEquals($serverTime, $resolvedEntity->serverTime);

        $scopeList = $resolvedEntity->getScopeList();
        $clusterList = $resolvedEntity->getClusterList();
        foreach ($scopeList as $scope) {
            $this->assertInstanceOf('\BO\Zmsentities\Scope', $scope);
        }
        foreach ($clusterList as $cluster) {
            $this->assertInstanceOf('\BO\Zmsentities\Cluster', $cluster);
        }
    }

    public function testGetImageName()
    {
        $entity = (new $this->entityclass())->getExample();
        $resolvedEntity = $entity->withResolvedCollections($this->getTestInput());
        $this->assertEquals('c_110_bild.jpg', $resolvedEntity->getImageName());

        $entity2 = (new $this->entityclass())->getExample();
        $resolvedEntity = $entity->withResolvedCollections(array(
            'scopelist' => '141',
        ));
        $this->assertEquals('s_141_bild.jpg', $resolvedEntity->getImageName());
    }

    protected function getTestInput()
    {
        return array(
            'scopelist' => '140,141',
            'clusterlist' => '110'
        );
    }

    protected function getTestScope()
    {
        return array(
            'id' => 142
        );
    }

    protected function getTestCluster()
    {
        return array(
            'id' => 109
        );
    }
}
