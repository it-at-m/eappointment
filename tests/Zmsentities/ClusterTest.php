<?php

namespace BO\Zmsentities\Tests;

class ClusterTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Cluster';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue('Bürger- und Standesamt' == $entity->getName(), 'getting cluster name failed');
    }
}
