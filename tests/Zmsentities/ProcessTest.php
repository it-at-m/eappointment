<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Appointment;
use \BO\Zmsentities\Availability;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Collection\AvailabilityList;
use \BO\Zmsentities\Collection\ProcessList;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 *
 */
class ProcessTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-01-01 12:50:00';

    public $entityclass = '\BO\Zmsentities\Process';

    public $collectionclass = '\BO\Zmsentities\Collection\ProcessList';

    public function testBasic()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = (new $this->entityclass())->getExample();

        $entity->addRequests('dldb', '122305');
        $this->assertContains('122305', $entity->getRequestCSV(), 'requests are not accessible');

        $entity->addScope('141');
        $this->assertTrue('141' == $entity->getScopeId(), 'scope is not accessible');
        $entity->setRandomAuthKey();
        $this->assertTrue(4 == strlen($entity->getAuthKey()), 'set random authKey failed');
        $this->assertTrue('Beispiel Termin' == $entity->getAmendment(), 'amendment is not accessible');

        $entity->setStatus('reserved');
        $this->assertTrue('reserved' == $entity->getStatus(), 'status is not accessible');

        $this->assertTrue(1447931730000 == $entity->getReminderTimestamp(), 'reminder timestamp is not set');

        $this->assertFalse($entity->isConfirmationSmsRequired(), 'Confirmation SMS should not be set to required');

        $this->assertContains('122305', (string)$entity, 'requests are not accessible');

        $this->assertTrue(2 == count($entity->getRequestIds()), 'requests are not accessible');
        $this->assertContains('122305', $entity->getRequestCsv(), 'requests are not accessible');
    }

    public function testClient()
    {
        $entity = (new $this->entityclass())->getExample();
        $firstClient = $entity->getFirstClient();
        $this->assertFalse($firstClient['surveyAccepted'], 'client update failed');
        $this->assertTrue('Max Mustermann' == $firstClient['familyName'], 'first client not found');
        $entity->getClients()[0] = null;
        $this->assertFalse($entity->getFirstClient()->hasEmail());
    }

    public function testAppointment()
    {
        $entity = (new $this->entityclass())->getExample();
        unset($entity->appointments);
        $entity->appointments = array();
        $firstAppointment = $entity->getFirstAppointment();
        $this->assertTrue($firstAppointment instanceof \BO\Zmsentities\Appointment);

        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $entity->addAppointment($appointment);
        $this->assertTrue($entity->hasAppointment(1447869172, 123), 'appointment is not accessible');
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');
    }

    public function testToCalendar()
    {
        $entity = (new $this->entityclass())->getExample();
        $calendar = $entity->toCalendar();
        $this->assertEntity('\BO\Zmsentities\Calendar', $calendar);
    }

    public function testCollection()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            1 == count($collection),
            'Missing new Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
        );

        $processListByTime = $collection->toProcessListByTime();
        $this->assertTrue(
            array_key_exists('1447869171', $processListByTime->sortByTimeKey()),
            'Failed to create process list by time'
        );
        $this->assertTrue(123456 == $collection->getFirstProcess()->id, 'First process not found in process list');
        $this->assertTrue(1 == count($collection->getAppointmentList()));

        $queueList = $collection->toQueueList($now);
        $this->assertEquals('queued', $queueList->getFirst()->status);
        $this->assertEquals('1447869171', $queueList->getFirst()->arrivalTime);
    }

    public function testScopeList()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scopeList = $collection->getScopeList();
        $this->assertTrue(count($scopeList) > 0);
    }

    public function testToQueue()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entityWithAppointment = $this->getExample();

        $dateWithoutTime = (new \DateTimeImmutable())->setTimestamp(1447801200);
        $entityWithoutAppointment = $this->getExample();
        $entityWithoutAppointment->addQueue(123, $dateWithoutTime);
        $entityWithoutAppointment->getFirstAppointment()->setTime($dateWithoutTime->format('Y-m-d H:i'));

        $queueWithAppointment = $entityWithAppointment->toQueue($now);
        $queueWithoutAppointment = $entityWithoutAppointment->toQueue($now);

        $this->assertTrue($queueWithAppointment->withAppointment);
        $this->assertFalse($queueWithoutAppointment->withAppointment);
    }

    public function testLessData()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $appointment->availability = (new \BO\Zmsentities\Availability())->getExample();
        $entity->appointments = (new \BO\Zmsentities\Collection\AppointmentList())->addEntity($appointment);
        $entity->scope = (new \BO\Zmsentities\Scope())->getExample();
        $dayoff = (new \BO\Zmsentities\Dayoff())->getExample();
        $entity->scope->dayoff = (new \BO\Zmsentities\Collection\DayOffList())->addEntity($dayoff);
        $collection->addEntity($entity);
        $collection = $collection->withLessData();
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertFalse(isset($entity->withLessData()['dayoff']), 'Converting to less data failed');
    }

    public function testAvailability()
    {
        $availability = new Availability([
            'id' => '93181',
            'weekday' => array (
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array (
                'afterWeeks' => '1',
            ),
            'workstationCount' => array (
                'public' => '1',
                'callcenter' => '1',
                'intern' => '1'
            ),
            'slotTimeInMinutes' => '15',
            'startDate' => strtotime('2016-04-19'),
            'endDate' => strtotime('2016-04-19'),
            'startTime' => '12:00:00',
            'endTime' => '16:00:00',
        ]);
        $availabilityList = new AvailabilityList([
            $availability
        ]);
        $processList = new ProcessList([
            new Process([
                'id' => '1',
                'appointments' => [
                    new Appointment([
                        'date' => strtotime('2016-04-19 12:15'),
                        'slotCount' => 1,
                    ])
                ],
                'status' => 'confirmed',
            ]),
            new Process([
                'id' => '2',
                'appointments' => [
                    new Appointment([
                        'date' => strtotime('2016-04-19 11:15'),
                        'slotCount' => 1,
                    ])
                ],
                'status' => 'confirmed',
            ]),
            new Process([
                'id' => '3',
                'appointments' => [
                    new Appointment([
                        'date' => strtotime('2016-04-19 12:15'),
                        'slotCount' => 1,
                    ])
                ],
                'status' => 'confirmed',
            ]),
        ]);
        $withAvailability = $processList->withAvailability($availability);
        $withStrict = $processList->withAvailabilityStrict($availability);
        $withOutAvailability = $processList->withOutAvailability($availabilityList);
        $this->assertEquals(2, $withAvailability->count(), "Wrong count ProcessList::withAvailability()");
        $this->assertEquals(1, $withStrict->count(), "Wrong count ProcessList::withAvailabilityStrict()");
        $this->assertEquals(
            strtotime('2016-04-19 12:15'),
            $withAvailability->getIterator()->current()->getFirstAppointment()->date
        );
        $this->assertEquals(2, $withOutAvailability->count(), "Wrong count ProcessList::withOutAvailability()");
        $this->assertEquals(
            strtotime('2016-04-19 11:15'),
            $withOutAvailability->getIterator()->current()->getFirstAppointment()->date
        );
    }

    //check if necessary
    /*
    public function testReduceWithinTime()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $entity->appointments = array($appointment);
        $collection->addEntity($entity);
        $collection->toReducedWithinTime(1447869172);
        $this->assertEntityList($this->entityclass, $collection);
    }
    */
}
