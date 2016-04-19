<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class CalendarTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,122238,122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        317869,324433,325341,324434,122281,324414,122283,122279,122246,122251,
        122257,122208,122226
    ];

    public function testFalkenhagener()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $input = $this->getTestEntity();
        //var_dump(json_encode($input, JSON_PRETTY_PRINT));
        $input->addProvider('dldb', 324414); // Falkenhagener Feld
        $entity = (new Query())->readResolvedEntity($input, $now);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 4, 19), "Missing 2016-04-19 in dataset");
        $this->assertEquals(1, $entity->getDay(2016, 4, 19)['freeAppointments']['public']);
    }

    public function testHeerstr()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $input = $this->getTestEntity();
        //var_dump(json_encode($input, JSON_PRETTY_PRINT));
        $input->addProvider('dldb', 122217); // Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $now);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 5, 23), "Missing 2016-05-23 in dataset");
        $this->assertEquals(0, $entity->getDay(2016, 5, 23)['freeAppointments']['public']);
        $this->assertTrue($entity->hasDay(2016, 5, 27), "Missing 2016-05-27 in dataset");
        $this->assertEquals(2, $entity->getDay(2016, 5, 27)['freeAppointments']['public']);
        $this->assertEquals(72, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
        $this->assertEquals(60, $entity->getDay(2016, 5, 31)['freeAppointments']['public']);
        // free day test
        // not implemented yet $this->assertEquals(0, $entity->getDay(2016, 5, 5)['freeAppointments']['public']);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testFullBAlist()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $input = $this->getTestEntity();
        //var_dump(json_encode($input, JSON_PRETTY_PRINT));
        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
        }
        $entity = (new Query())->readResolvedEntity($input, $now);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    protected function getTestEntity()
    {
        $time = new \DateTimeImmutable("2016-04-01 11:55");
        $input = new Entity(array(
            "firstDay" => [
                "year" => $time->format('Y'),
                "month" => $time->format('m'),
                "day" => $time->format('d')
                ],
            "lastDay" => [
                "year" => $time->modify('+1 month')->format('Y'),
                "month" => $time->modify('+1 month')->format('m'),
                "day" => $time->modify('+1 month')->format('t')
                ],
            "requests" => [
                [
                    "source" => "dldb",
                    "id" => "120703",
                    ],
                ],
        ));
        return $input;
    }
}
