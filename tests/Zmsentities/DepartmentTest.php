<?php

namespace BO\Zmsentities\Tests;

use BO\Zmsentities\Collection\ClusterList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Cluster;
use BO\Zmsentities\Scope;

class DepartmentTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Department';

    public function testClusterDuplicates()
    {
        $example = $this->getExample();
        $example['scopes'] = new ScopeList();
        $cluster = new Cluster();
        $scope = new Scope(['id' => 1234]);
        $cluster['scopes'][] = $scope;
        $example['clusters'][] = $cluster;
        $example['scopes'][] = $scope;
        $reduced = $example->withOutClusterDuplicates();
        $this->assertTrue($example['scopes']->hasEntity(1234));
        $this->assertFalse($reduced['scopes']->hasEntity(1234));
        $this->assertTrue($example['clusters'][0]['scopes']->hasEntity(1234));
        $this->assertTrue($reduced['clusters'][0]['scopes']->hasEntity(1234));
        //var_dump(json_decode(json_encode($example)));
        //var_dump(json_decode(json_encode($reduced)));
    }
}
