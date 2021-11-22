<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Query\SlotList;
use \BO\Zmsentities\Collection\SlotList as Collection;

class SlotListTest extends Base
{
    public function testBasic()
    {
        $now = static::$now;
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', '2016-04-05');
        $slotList = new SlotList($this->getTestSlotData(), $dateTime, $dateTime->modify('+1day'), $now);
        $slotList->setSlotData($this->getTestSlotList());
        $this->assertStringContainsString('SlotList: Availability.appointment #68979 starting', (string)$slotList);
    }

    public function testExceptionEmpty()
    {
        $this->expectException('\BO\Zmsdb\Exception\SlotDataEmpty');
        $slotList = new SlotList();
        $slotList->addQueryData(array());
    }

    public function testExceptionWithoutPreGeneratedSlot()
    {
        $this->expectException('\BO\Zmsdb\Exception\SlotDataWithoutPreGeneratedSlot');
        $slotData = $this->getTestSlotData();
        $slotData['slotdate'] = (new \DateTimeImmutable())->format('Y-m-d');
        $slotList = new SlotList();
        $slotList->addQueryData($slotData);
    }

    public function testExceptionWithoutPreGeneratedSlot2()
    {
        $this->expectException('\BO\Zmsdb\Exception\SlotDataWithoutPreGeneratedSlot');
        $slotData = $this->getTestSlotData();
        $now = static::$now;
        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', '2016-04-05');
        $slotList = new SlotList($slotData, $dateTime, $dateTime->modify('+1day'), $now);
        $slotList->addQueryData(array(
            'slottime' => '11:10:00',
            'slotdate' => '2016-04-05',
            'slotnr' => '9999' //unavailable slotnumber
        ));
    }

    protected function getTestSlotData()
    {
        return array (
            'appointment__date' => '1459846800',
            'appointment__scope__id' => '141',
            'appointment__scope__preferences__appointment__multipleSlotsEnabled' => '0',
            'appointment__scope__dayoff__0__date' => '1463868000',
            'appointment__scope__dayoff__0__name' => 'Dummy freier Tag',
            'day' => '5',
            'month' => '4',
            'year' => '2016',
            'slottime' => '11:00:00',
            'slotdate' => '2016-04-05',
            'freeAppointments__public' => '0',
            'freeAppointments__callcenter' => '0',
            'freeAppointments__intern' => '0',
            'slotnr' => '0',
            'availability__id' => '68979',
            'availability__multipleSlotsAllowed' => '0',
            'availability__repeat__afterWeeks' => '1',
            'availability__repeat__weekOfMonth' => '0',
            'availability__slotTimeInMinutes' => '10',
            'availability__startDate' => '1453845600',
            'availability__endDate' => '1463868000',
            'availability__startTime' => '11:00:00',
            'availability__endTime' => '17:50:00',
            'availability__weekday__monday' => '0',
            'availability__weekday__tuesday' => '4',
            'availability__weekday__wednesday' => '0',
            'availability__weekday__thursday' => '0',
            'availability__weekday__friday' => '0',
            'availability__weekday__saturday' => '0',
            'availability__weekday__sunday' => '0',
            'availability__workstationCount__public' => '3',
            'availability__workstationCount__callcenter' => '3',
            'availability__workstationCount__intern' => '3',
            'availability__bookable__startInDays' => '0',
            'availability__bookable__endInDays' => '60',
            'availability__scope__dayoff__0__date' => '1463868000',
            'availability__scope__dayoff__0__name' => 'Dummy freier Tag',
        );
    }

    protected function getTestSlotList()
    {
        return array(
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:00',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:10',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:20',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:30',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:40',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '11:50',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '12:00',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '12:10',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '12:20',
            ),
            array(
             'public' => '3',
             'intern' => '3',
             'callcenter' => '3',
             'type' => 'free',
             'time' => '12:30',
            )
        );
    }
}
