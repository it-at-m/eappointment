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
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertEquals(120335, $entity['id']);

        //source not dldb
        $entity = (new Query())->readEntity('test', 122280, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
    }

    public function testListByProvider()
    {
        //Dienstleister Bürgeramt I in Köpenick
        $collection = (new Query())->readListByProvider('dldb', 122208, 1);
        $this->assertEntityList("\\BO\\Zmsentities\\Request", $collection);
        $this->assertEquals(true, $collection->hasEntity('120335')); //Abmeldung einer Wohnung

    }
}
