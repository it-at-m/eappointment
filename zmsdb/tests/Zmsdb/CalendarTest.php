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

    public function testExceptionCalendarWithoutScopes()
    {
        $this->expectException('\BO\Zmsdb\Exception\CalendarWithoutScopes');
        $input = $this->getTestEntity();
        (new Query())->readResolvedEntity($input, static::$now);
    }

    public function testWithScope()
    {
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        $this->assertTrue($entity->hasDay(2016, 5, 27), "Missing 2016-05-27 in dataset");
        $this->assertEquals(2, $entity->getDay(2016, 5, 27)['freeAppointments']['public']);
    }

    public function testDayOffBASpandau()
    {
        //Bürgeramt Spandau with Day Off on 2016-05-25
        $freeProcessesDate = new \DateTimeImmutable("2016-05-25");
        $input = $this->getTestEntity();
        $input->addCluster(109); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, static::$now, $freeProcessesDate);
        $this->assertEquals(0, count($entity['freeProcesses']));
    }

    public function testFalkenhagener()
    {
        $input = $this->getTestEntity();
        //var_dump(json_encode($input, JSON_PRETTY_PRINT));
        $input->addProvider('dldb', 324414); // Falkenhagener Feld
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 4, 19), "Missing 2016-04-19 in dataset");
        $this->assertEquals(1, $entity->getDay(2016, 4, 19)['freeAppointments']['public']);
    }

    public function testHeerstr()
    {
        $input = $this->getTestEntity();
        //var_dump(json_encode($input, JSON_PRETTY_PRINT));
        $input->addProvider('dldb', 122217); // Bürgeramt Heerstr.
        $input->addCluster(109); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        //var_dump("$entity");
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 5, 23), "Missing 2016-05-23 in dataset");
        $this->assertEquals(0, $entity->getDay(2016, 5, 23)['freeAppointments']['public']);
        $this->assertTrue($entity->hasDay(2016, 5, 27), "Missing 2016-05-27 in dataset");
        $this->assertEquals(2, $entity->getDay(2016, 5, 27)['freeAppointments']['public']);
        $this->assertEquals(72, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
        $this->assertFalse($entity->hasDay(2016, 5, 31), "Should not have 31.05. beeing 61 days in the future");
        // free day test
        $this->assertEquals(0, $entity->getDay(2016, 5, 5)['freeAppointments']['public']);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }

    public function testZwickauerDamm()
    {
        $input = $this->getTestEntity();
        $input->addProvider('dldb', 122296); // Bürgeramt Zwickauer Damm
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 4, 25), "Missing 2016-04-25 in dataset");
        $this->assertEquals(
            0,
            $entity->getDay(2016, 4, 25)['freeAppointments']['public'],
            "Opening Hour 'einmaliger Termin' failed"
        );
    }

    public function testMultipleSlots()
    {
        $input = $this->getTestEntity();
        $input->addProvider('dldb', 122271); // Bürgeramt Biesdorf Center
        $input->addRequest('dldb', 120335); // slots = 2 + Perso slots = 2 -> slots = 4
        $entity = (new Query())->readResolvedEntity($input, static::$now);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        $this->assertTrue($entity->hasDay(2016, 5, 25), "Missing 2016-05-25 in dataset");
        $this->assertEquals(
            20,
            $entity->getDay(2016, 5, 26)['freeAppointments']['public'],
            "wrong calculated slotsRequired or constraint is ignored"
        );
    }

    public function testWithoutSlotsRequiredForce()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-30");
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, null, 'public');
        $this->assertEquals(72, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
    }

    public function testWithSlotsRequiredForce()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-30");
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, null, 'public', 3);
        $this->assertEquals(60, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
    }

    public function testWithSlotsRequiredFloat1()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-30");
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, null, 'public', 0.4);
        $this->assertEquals(72, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
    }

    public function testWithSlotsRequiredFloat2()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-30");
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, null, 'public', 2.6);
        $this->assertEquals(60, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
    }

    public function testWithSlotsRequiredFloat3()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-30");
        $input = $this->getTestEntity();
        $input->addScope(141); // Bürgeramt Heerstr.
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, null, 'public', 3.4);
        $this->assertEquals(60, $entity->getDay(2016, 5, 30)['freeAppointments']['public']);
    }

    public function testOverallDayOff()
    {
        $freeProcessesDate = new \DateTimeImmutable("2016-05-16");
        $input = $this->getTestEntity();
        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
        }
        $entity = (new Query())->readResolvedEntity($input, $freeProcessesDate, $freeProcessesDate);
        $this->assertEquals(0, count($entity['freeProcesses']));
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
                    "id" => "120703", // Personalausweis beantragen
                    ],
                ],
        ));
        return $input;
    }
}
