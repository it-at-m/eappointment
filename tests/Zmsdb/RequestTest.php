<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Request as Query;

use BO\Zmsdb\Request;

class RequestTest extends Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 120335);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertEquals(120335, $entity['id']);

        $entity = (new Query())->readEntity('dldb', 120335, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertTrue(array_key_exists('data', $entity), 'Addional data attribute missed');

        $class = new \BO\Zmsdb\Query\Request(\BO\Zmsdb\Query\Base::SELECT);
        $name = $class->getName();
        $this->assertEquals('BO\Zmsdb\Query\Request', $name);
    }

    public function testSolution10Query()
    {
        $class = new \BO\Zmsdb\Query\Request((new \Solution10\SQL\Select));
        $this->assertEquals('request', $class->getAlias());
    }

    public function testExceptionRequestNotFound()
    {
        $this->expectException("\\BO\\Zmsdb\\Exception\\RequestNotFound");
        (new Query())->readEntity('dldb', 999999);
    }

    public function testExceptionUnknownDataSource()
    {
        $this->expectException("\\BO\\Zmsdb\\Exception\\UnknownDataSource");
        //source not dldb
        $entity = (new Query())->readEntity('test', 122280, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
    }

    public function testListByProvider()
    {
        //Dienstleister Bürgeramt I in Köpenick
        $collection = (new Query())->readListByProvider('dldb', 122208, 0);
        $this->assertEntityList("\\BO\\Zmsentities\\Request", $collection);
        $this->assertEquals(true, $collection->hasEntity('120335')); //Abmeldung einer Wohnung
    }

    public function testListByCluster()
    {
        //Cluster Rathaus Schöneberg
        $cluster = (new \BO\Zmsdb\Cluster())->readEntity(4, 2);
        $collection = (new Query)->readListByCluster($cluster);
        $this->assertEntityList("\\BO\\Zmsentities\\Request", $collection);
    }

    public function testResolveReferencesFailed()
    {
        $this->expectException('\Exception');
        (new Query)->readEntity('dldb', 120335, null);
    }
}
