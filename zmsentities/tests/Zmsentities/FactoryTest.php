<?php

namespace BO\Zmsentities\Tests;

class FactoryTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Session';

    public function testBasic()
    {
        $data = $this->getExampleData();
        error_log(json_encode($data));
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
        return array(
            '$schema' => 'https://schema.berlin.de/queuemanagement/session.json',
            'id' => '9f9afefb51ddd482233c17d1cc90e442',
            'name' => 'Zmsappointment',
            'content' => unserialize(
                'a:4:{
                    s:6:"status";s:5:"start";
                    s:6:"basket";a:7:{
                        s:8:"requests";s:6:"120703";
                        s:9:"providers";s:6:"122217";
                        s:5:"scope";s:3:"123";
                        s:7:"process";s:7:"1234567";
                        s:4:"date";s:10:"1456310041";
                        s:8:"firstDay";s:10:"2016-04-01";
                        s:7:"lastDay";s:10:"2016-05-31";
                    }
                    s:5:"entry";a:3:{
                        s:6:"source";s:6:"reinit";
                        s:9:"providers";s:6:"122217";
                        s:8:"requests";s:6:"120703";
                    }
                    s:5:"human";a:5:{
                        s:4:"step";a:1:{s:9:"dayselect";i:6;}
                        s:6:"client";i:1;
                        s:2:"ts";i:1474531960;
                        s:6:"origin";s:5:"pixel";
                        s:13:"remoteAddress";s:9:"127.0.0.1";
                    }
                }'
            )
        );        
    }
}
