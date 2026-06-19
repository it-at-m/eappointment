<?php

namespace BO\Zmsbackend\Tests\Request\Service;

use \BO\Zmsbackend\Request\Service\Request as Query;

use BO\Zmsbackend\Request\Service\Request;

class RequestTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $entity = (new Query())->readEntity('dldb', 120335);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertEquals(120335, $entity['id']);

        $entity = (new Query())->readEntity('dldb', 120335, 1);
        $this->assertEntity("\\BO\\Zmsentities\\Request", $entity);
        $this->assertArrayHasKey('data', (array) $entity, 'Addional data attribute missed');

        $class = new \BO\Zmsbackend\Request\Repository\Request(\BO\Zmsbackend\Query\Base::SELECT);
        $name = $class->getName();
        $this->assertEquals('BO\Zmsbackend\Request\Repository\Request', $name);
    }

    public function testSolution10Query()
    {
        $class = new \BO\Zmsbackend\Request\Repository\Request((new \BO\Zmsbackend\Query\Builder\Select));
        $this->assertEquals('request', $class->getAlias());
    }

    public function testUnknowSource()
    {
        $this->expectException('BO\Zmsbackend\Source\Exception\UnknownDataSource');
        (new Query())->readEntity('xxx', 122280, 1);
    }

    public function testExceptionRequestNotFound()
    {
        $this->expectException("\\BO\\Zmsbackend\\Request\\Exception\\RequestNotFound");
        (new Query())->readEntity('dldb', 999999);
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
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity(4, 2);
        $collection = (new Query)->readListByCluster($cluster);
        $this->assertEntityList("\\BO\\Zmsentities\\Request", $collection);
    }

    public function testResolveReferencesFailed()
    {
        $this->expectException('\Exception');
        (new Query)->readEntity('dldb', 120335, null);
    }

    public function testWriteEntity()
    {
        $query = new Query();
        $entity = (new \BO\Zmsentities\Request())->getExample();
        $entity = $query->writeEntity($entity);
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals(120335, $entity->getId());
    }

    public function testWriteEntityFailed()
    {
        $this->expectException('BO\Zmsbackend\Request\Exception\RequestNotFound');
        $query = new Query();
        $entity = (new \BO\Zmsentities\Request())->getExample();
        unset($entity['id']);
        $query->writeEntity($entity);
    }

    public function testWriteImport()
    {
        $query = new Query();
        $repository = (new \BO\Zmsdldb\FileAccess())->loadFromPath(\BO\Zmsbackend\Source\Zmsdldb::$importPath);
        $importInput = $repository->fromService()->fetchId(120335);
        $importInput['group'] = 'test';
        $entity = $query->writeImportEntity($importInput, 'dldb'); //return written entity by true
        $this->assertEquals('dldb', $entity->getSource());
        $this->assertEquals(120335, $entity->getId());
        $this->assertEquals('test', $entity->group);
    }
}
