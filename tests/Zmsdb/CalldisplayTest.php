<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calldisplay as Query;
use \BO\Zmsentities\Calldisplay as Entity;

class CalldisplayTest extends Base
{
    public function testBasic()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestEntity();
        $entity = $query->readResolvedEntity($input, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Calldisplay", $entity);

        $organisation = $query->readResolvedOrganisation($entity);
        $this->assertEntity("\\BO\\Zmsentities\\Organisation", $organisation);

        $image = $query->readImage($entity);
        $this->assertEquals('png', $image['mime']);

        $contact = $query->readContactData($entity);
        $this->assertEquals('Bürgeramt', $contact['name']);
    }

    protected function getTestEntity()
    {
        return $input = (new Entity())->getExample();
    }
}
