<?php

namespace BO\Zmsentities\Tests;

class AvailabilityTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Availability';

    public function testHasDay()
    {
        $entity = new $this->entityclass();
        $time = new \DateTimeImmutable('2016-01-01 12:34:56');
        $entity['startDate'] = $time->getTimestamp();
        $entity['startTime'] = $time->format('H:i');
        $entity['endDate'] = $time->modify("+2month")->getTimestamp();
        $entity['endTime'] = $time->modify("+2month 17:10:00")->format('H:i');
        $entity['weekday']['friday'] = 1;
        $entity['repeat']['afterWeeks'] = 2;
        //var_dump($entity);
        $this->assertTrue(
            $entity->hasDate($time),
            'Availability should be valid on startDate'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+3days')),
            'Availability should not be valid on a monday if only friday is given'
        );
        $entity['weekday']['monday'] = 1;
        $this->assertTrue(
            $entity->hasDate($time->modify('+3days')),
            'Availability should be valid on a monday if friday and monday is given'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+1week')),
            'Availability should not be valid in the first week afterwards if repeating is set to every 2 weeks'
        );
        $this->assertTrue(
            $entity->hasDate($time->modify('+2week')),
            'Availability should be valid in the second week afterwards'
        );
        $entity['repeat']['weekOfMonth'] = 2;
        $this->assertTrue(
            $entity->hasDate($time->modify('+1week')),
            'Availability should be valid in the second week of the month'
        );
        $this->assertFalse(
            $entity->hasDate($time->modify('+3week')),
            'Availability should be valid in the third week of the month'
        );
        $this->assertFalse(
            $entity->isBookable($time, $time),
            'Availability default should be, that you cannot reserve an appointment for today'
        );
        $this->assertTrue(
            $entity->isBookable($time->modify('+1day'), $time),
            'Availability default should be, that you cannot reserve an appointment for the next day'
        );
        $entity['startDate'] = $time->modify('+1day')->getTimestamp();
        $entity['repeat']['afterWeeks'] = 2;
        $entity['repeat']['weekOfMonth'] = 0;
        $this->assertTrue(
            $entity->hasDate($time->modify('+3week')),
            'Availability on afterWeeks=2 should be valid in the third week after startDate +1 day'
        );
    }

    public function testSlotList()
    {
        $slotListResult = [
            0 => array (
                'time' => '12:00',
                'public' => 0,
                'callcenter' => 0,
                'intern' => 3,
            ),
            1 => array (
                'time' => '13:30',
                'public' => 0,
                'callcenter' => 0,
                'intern' => 3,
            ),
            2 => array (
                'time' => '15:00',
                'public' => 0,
                'callcenter' => 0,
                'intern' => 3,
            ),
            3 => array (
                'time' => '16:30',
                'public' => 0,
                'callcenter' => 0,
                'intern' => 3,
            ),
            // If the last slot is equal to the stop time, there should not be a slot! (Do not remove this comment)
            //4 => array (
            //    'time' => '18:00',
            //    'public' => 0,
            //    'callcenter' => 0,
            //    'intern' => 3,
            //),
        ];
        $time = new \DateTimeImmutable('12:00:00');
        $entity = new $this->entityclass([
            'startTime' => $time->format('H:i'),
            'endTime' => $time->modify("18:00:00")->format('H:i'),
            'slotTimeInMinutes' => 90,
        ]);
        $entity['workstationCount']['intern'] = 3;
        $slotList = $entity->getSlotList();
        $this->assertEquals($slotList, $slotListResult);
        $entity['slotTimeInMinutes'] = 0;
        $slotList = $entity->getSlotList();
        $this->assertEquals($slotList, []);
        //var_dump((string)$entity);
    }

    public function testSlotListRealExample()
    {
        $entity = new $this->entityclass([
            'id' => '93181',
            'weekday' =>
            array (
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0',
            ),
            'repeat' =>
            array (
                'afterWeeks' => '2',
                'weekOfMonth' => '0',
            ),
            'bookable' =>
            array (
                'startInDays' => '0',
                'endInDays' => '60',
            ),
            'workstationCount' =>
            array (
                'public' => '2',
                'callcenter' => '2',
                'intern' => '2',
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => '1461024000',
            'endDate' => '1461024000',
            'startTime' => '12:00:00',
            'endTime' => '16:00:00',
            'multipleSlotsAllowed' => '0',
        ]);
        $slotList = $entity->getSlotList();
        $this->assertTrue(16 == count($slotList));
        $this->assertTrue($entity->hasDate(new \DateTime('2016-04-19')));
    }
}
