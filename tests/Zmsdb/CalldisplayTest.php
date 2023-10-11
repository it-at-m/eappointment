<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calldisplay as Query;
use \BO\Zmsentities\Calldisplay as Entity;

class CalldisplayTest extends Base
{
    /*
    public function testBasic()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->readResolvedEntity($input, static::$now);
        $this->assertEntity("\\BO\\Zmsentities\\Calldisplay", $entity);

        $organisation = $query->readResolvedOrganisation($entity);
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $organisation);

        $image = $query->readImage($entity);
        $this->assertEquals('png', $image['mime']);

        $contact = $query->readContactData($entity);
        $this->assertEquals('Bürgeramt', $contact['name']);
    }
    
    public function testBasicWithCluster()
    {
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity();
        $cluster = (new \BO\Zmsentities\Cluster())->getExample();
        $cluster->id = 110;
        $cluster->scopes = $input->scopes;
        $input->clusters[] = $cluster;
        $input->scopes = new \BO\Zmsentities\Collection\ScopeList();
        $entity = $query->readResolvedEntity($input, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Calldisplay", $entity);

        $organisation = $query->readResolvedOrganisation($entity);
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $organisation);

        $contact = $query->readContactData($entity);
        $this->assertEquals('Bürgeramt Hohenzollerndamm', $contact['name']);
    }

    protected function getTestEntity()
    {
        return (new Entity())->getExample();
    }
    */
}
