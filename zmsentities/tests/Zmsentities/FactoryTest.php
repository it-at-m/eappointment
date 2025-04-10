<?php

namespace BO\Zmsentities\Tests;

class FactoryTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Session';

    public function testBasic()
    {
        $data = $this->getExampleData();
        $factory = \BO\Zmsentities\Schema\Factory::create($data);
        $this->assertEntity('\BO\Zmsentities\Session', $factory->getEntity());
        $this->assertTrue('Session' == $factory->getEntityName(), 'Failed to get EntityName of factored entity');
    }

    public function testException()
    {
        $data = $this->getExampleData();
        unset($data['$schema']);
        $factory = \BO\Zmsentities\Schema\Factory::create($data);
        try {
            $factory->getEntityName();
            $this->fail("Expected exception SchemaMissingKey not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaMissingKey $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    protected function getExampleData()
    {
        $data = [
            "status" => "start",
            "basket" => [
                "requests" => "120703",
                "providers" => "122217",
                "scope" => "123",
                "process" => "1234567",
                "date" => "1456310041",
                "firstDay" => "2016-04-01",
                "lastDay" => "2016-05-31",
            ],
            "entry" => [
                "source" => "reinit",
                "providers" => "122217",
                "requests" => "120703",
            ],
            "human" => [
                "step" => ["dayselect" => 6],
                "client" => 1,
                "ts" => 1474531960,
                "origin" => "pixel",
                "remoteAddress" => "127.0.0.1",
            ]
        ];

        return array(
            '$schema' => 'https://schema.berlin.de/queuemanagement/session.json',
            'id' => '9f9afefb51ddd482233c17d1cc90e442',
            'name' => 'Zmsappointment',
            'content' => $data
        );        
    }
}
