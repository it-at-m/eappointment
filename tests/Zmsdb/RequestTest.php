<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Request as Query;

use BO\Zmsdb\Request;

class RequestTest extends Base
{
    public function testBasic()
    {
        /*
        $connection = \Mockery::mock("PDO");
        $connection->shouldReceive('fetchOne')
            ->andReturn([
                  'id' => '120335',
                  'link' => 'https://service.berlin.de/dienstleistung/120335/',
                  'name' => 'Abmeldung einer Wohnung',
                  'source' => 'dldb',
            ]);
        */
        $connection = null;
        $entity = (new Query($connection, $connection))->readEntity('dldb', 120335);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertEquals(120335, $entity['id']);
    }
}
