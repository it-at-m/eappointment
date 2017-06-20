<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Appointment;
use \BO\Zmsentities\Availability;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Collection\AvailabilityList;
use \BO\Zmsentities\Collection\ProcessList;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(Public)
 * @SuppressWarnings(TooManyMethods)
 *
 */
class ProcessTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-01-01 12:50:00';

    public $entityclass = '\BO\Zmsentities\Process';

    public $collectionclass = '\BO\Zmsentities\Collection\ProcessList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertTrue($entity->hasProcessCredentials());
        $this->assertTrue($entity->hasQueueNumber());

        $entity->addRequests('dldb', '122305');
        $this->assertContains('122305', $entity->getRequestCSV(), 'requests are not accessible');

        $entity->addScope('141');
        $this->assertTrue('141' == $entity->getScopeId(), 'scope is not accessible');
        $entity->setRandomAuthKey();
        $this->assertTrue(4 == strlen($entity->getAuthKey()), 'set random authKey failed');
        $this->assertTrue('Beispiel Termin' == $entity->getAmendment(), 'amendment is not accessible');

        $entity->setStatus('reserved');
        $this->assertTrue('reserved' == $entity->getStatus(), 'status is not accessible');

        $this->assertTrue(1447931730 == $entity->getReminderTimestamp(), 'reminder timestamp is not set');

        $this->assertFalse($entity->isConfirmationSmsRequired(), 'Confirmation SMS should not be set to required');

        $this->assertContains('122305', (string)$entity, 'requests are not accessible');

        $this->assertTrue(2 == count($entity->getRequestIds()), 'requests are not accessible');
        $this->assertContains('122305', $entity->getRequestCsv(), 'requests are not accessible');

        $this->assertFalse($entity->hasScopeAdmin());
    }

    public function testClient()
    {
        $entity = $this->getExample();
        $firstClient = $entity->getFirstClient();
        $this->assertFalse($firstClient['surveyAccepted'], 'client update failed');
        $this->assertTrue('Max Mustermann' == $firstClient['familyName'], 'first client not found');
        unset($entity->getClients()[0]);
        $this->assertFalse($entity->getFirstClient()->hasEmail());
    }

    public function testAppointment()
    {
        $entity = $this->getExample();
        unset($entity->appointments);
        $entity->appointments = array();
        $firstAppointment = $entity->getFirstAppointment();
        $this->assertTrue($firstAppointment instanceof \BO\Zmsentities\Appointment);

        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $entity->addAppointment($appointment);
        $this->assertTrue($entity->hasAppointment(1447869172, 123), 'appointment is not accessible');
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');

        $appointment = (new \BO\Zmsentities\Appointment())->getExample()->getArrayCopy();
        $entity->appointments = array($appointment);
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');
    }

    public function testCallTime()
    {
        $entity = $this->getExample();
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity->setCallTime($now);
        $this->assertEquals('12:50:00', $entity->getCallTimeString());
    }

    public function testSetClientsCount()
    {
        $entity = $this->getExample();
        $entity->setClientsCount(3);
        $this->assertEquals(3, $entity->getClients()->count());
    }

    public function testSetStatusBySettings()
    {
        $entity = $this->getExample();
        $entity->setStatusBySettings();
        $this->assertEquals('confirmed', $entity->status);

        $entity->status = 'called';
        $entity->queue['callCount'] = 1;
        $entity->setStatusBySettings();
        $this->assertEquals('missed', $entity->status);

        $entity->status = 'pickup';
        $entity->setStatusBySettings();
        $this->assertEquals('queued', $entity->status);
    }

    public function testWithoutPersonalData()
    {
        $entity = $this->getExample();
        $entity = $entity->withoutPersonalData();
        $this->assertFalse($entity->toProperty()->clients->isAvailable());
        $this->assertFalse($entity->toProperty()->appointments->isAvailable());
    }

    public function testGetWaitedSeconds()
    {
        $entity = $this->getExample();
        $seconds = $entity->getWaitedSeconds();
        $this->assertEquals(45, $seconds);
        $this->assertTrue(0 < $seconds);

        $entity->queue['callTime'] = null;
        $this->assertEquals(null, $entity->getWaitedSeconds());
    }

    public function testToDereferencedAmendment()
    {
        $entity = $this->getExample();
        $amendment = $entity->toDerefencedAmendment();
        $this->assertContains("LastChange' => '2015-11-19T12:13:16+01:00'", $amendment);
    }

    public function testGetRequests()
    {
        $entity = $this->getExample();
        $entity->requests = array((new \BO\Zmsentities\Request)->getExample());
        $requestList = $entity->getRequests();
        $this->assertEntityList('\BO\Zmsentities\Request', $requestList);
    }

    public function testUpdateRequests()
    {
        $entity = $this->getExample();
        $requestCSV = 999999;
        $entity->updateRequests('dldb', $requestCSV);
        $requestList = $entity->getRequests();
        $this->assertTrue($requestList->hasEntity(999999));
    }

    public function testWithFormDataUpdateFromAdmin()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $scope = (new \BO\Zmsentities\Scope)->getExample();
        $scope->preferences['client']['emailRequired'] = 1;
        $scope->preferences['client']['telephoneRequired'] = 1;

        $validator = new \BO\Mellon\Validator([
            'familyName' => 'Max Mustermann',
            'email' => 'zms@berlinonline.de',
            'telephone' => '0123456789',
            'requests' => [120335,120697],
            'surveyAccepted' => 1,
            'sendConfirmation' => 1,
            'sendMailConfirmation' => 1,
            'sendReminder' => 1,
            'amendment' => 'Test Zusatz'
        ]);
        $validator->makeInstance();

        $formCollection = \BO\Zmsentities\Helper\ProcessFormValidation::fromAdminParameters($scope->preferences);
        $input = [
            'headsUpTime' => 60,
            'slotCount' => 1
        ];
        $scope = (new \BO\Zmsentities\Scope)->getExample();
        $entity->withUpdatedData($formCollection->getStatus(), $input, $scope, $now);
        $this->assertTrue($entity->requests->hasEntity(120335));
        $this->assertTrue($entity->requests->hasEntity(120697));
        $this->assertEquals(1451648940, $entity->reminderTimestamp);
    }

    public function testAddClientFromForm()
    {
        $entity = $this->getExample();
        $scope = (new \BO\Zmsentities\Scope)->getExample();
        $validator = new \BO\Mellon\Validator([
            'familyName' => 'Max Mustermann',
            'email' => 'zms@berlinonline.de',
            'telephone' => '0123456789',
            'requests' => [120335,120697],
            'surveyAccepted' => 1,
            'amendment' => 'Test Zusatz',
            'agbgelesen' => 1
        ]);
        $validator->makeInstance();
        $formCollection = \BO\Zmsentities\Helper\ProcessFormValidation::fromParameters($scope->preferences);
        $entity->addClientFromForm($formCollection->getStatus());
        $this->assertEquals('Max Mustermann', $entity->getClients()->getFirst()->familyName);
        $this->assertEquals('zms@berlinonline.de', $entity->getClients()->getFirst()->email);
    }

    public function testFromManageProcess()
    {
        $validator = new \BO\Mellon\Validator([
            'process' => 10029,
            'authKey' => '1c56'
        ]);
        $validator->makeInstance();
        $result = \BO\Zmsentities\Helper\ProcessFormValidation::fromManageProcess();
        $formdata = $result->getStatus();
        $this->assertFalse($formdata['process']['failed']);
        $this->assertFalse($formdata['authKey']['failed']);
    }

    public function testFromManageProcessFailed()
    {
        $validator = new \BO\Mellon\Validator([
            'process' => 10029,
            'authKey' => ''
        ]);
        $validator->makeInstance();
        $result = \BO\Zmsentities\Helper\ProcessFormValidation::fromManageProcess();
        $formdata = $result->getStatus();
        $this->assertFalse($formdata['process']['failed']);
        $this->assertTrue($formdata['authKey']['failed']);
    }

    public function testFromParamsToProcess()
    {
        $entity = $this->getExample();
        $scope = (new \BO\Zmsentities\Scope)->getExample();
        $entity->scope = $scope;
        $validator = new \BO\Mellon\Validator([
            'form_validate' => 1,
            'familyName' => 'Max Mustermann',
            'email' => 'zms@berlinonline.de',
            'telephone' => '0123456789',
            'surveyAccepted' => 1,
            'amendment' => 'Test Zusatz',
            'agbgelesen' => 1,
            'sendReminder' => 1,
            'headsUpTime' => 60
        ]);
        $validator->makeInstance();
        $result = \BO\Zmsentities\Helper\ProcessFormValidation::fromParametersToProcess($entity);
        $this->assertEntity('\BO\Zmsentities\Process', $result['process']);
    }

    public function testFromParamsToProcessFailed()
    {
        $entity = $this->getExample();
        $entity->scope = (new \BO\Zmsentities\Scope)->getExample();
        $validator = new \BO\Mellon\Validator([
            'form_validate' => 1
        ]);
        $validator->makeInstance();
        $result = \BO\Zmsentities\Helper\ProcessFormValidation::fromParametersToProcess($entity);
        $this->assertTrue($result['formdata']['failed']);
    }

    public function testFromParamsToProcessUnvalid()
    {
        $entity = $this->getExample();
        $entity->scope = (new \BO\Zmsentities\Scope)->getExample();
        $validator = new \BO\Mellon\Validator([
        ]);
        $validator->makeInstance();
        $result = \BO\Zmsentities\Helper\ProcessFormValidation::fromParametersToProcess($entity);
        $this->assertTrue(null === $result['formdata']);
    }

    public function testToCalendar()
    {
        $entity = $this->getExample();
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

        $entity2 = $this->getExample();
        $entity2->getFirstAppointment()->date = 1459511423;
        $collection->addEntity($entity2);

        $processListByTime = $collection->toProcessListByTime();
        $this->assertTrue(
            array_key_exists('1447869171', $processListByTime->sortByTimeKey()),
            'Failed to create process list by time'
        );
        $this->assertEquals(123456, $collection->getFirst()->id, 'First process not found in process list');
        $this->assertEquals(2, count($collection->getAppointmentList()));

        $queueList = $collection->toQueueList($now);
        $this->assertEquals('queued', $queueList->getFirst()->status);
        $this->assertEquals('1447869171', $queueList->getFirst()->arrivalTime);
    }

    public function testProcessListByStatusAndTime()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);

        $entity2 = $this->getExample();
        $entity2->getFirstAppointment()->date = 1459511423;
        $collection->addEntity($entity2);
        $list = $collection->toProcessListByStatusAndTime();
        $this->assertEntityList('\BO\Zmsentities\Process', $list[13][1459511423]);
    }

    public function testScopeList()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scopeList = $collection->getScopeList();
        $this->assertTrue(count($scopeList) > 0);
    }

    public function testWithScopeId()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $entity2 = $this->getExample();
        $entity2->scope['id'] = 141;
        $collection->addEntity($entity2);
        $this->assertEquals(1, $collection->withScopeId(123)->count());
        $this->assertEquals(1, $collection->withOutScopeId(141)->count());
    }

    public function testToQueue()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $withAppointment = $this->getExample();

        $dateWithoutTime = (new \DateTimeImmutable())->setTimestamp(1447801200);
        $withoutAppointment = $this->getExample();
        $withoutAppointment->addQueue(123, $dateWithoutTime);
        $withoutAppointment->getFirstAppointment()->setTime($dateWithoutTime->format('Y-m-d H:i'));

        $queueWith = $withAppointment->toQueue($now);
        $queueWithout = $withoutAppointment->toQueue($now);

        $this->assertTrue($queueWith->withAppointment);
        $this->assertFalse($queueWithout->withAppointment);
    }

    public function testPickup()
    {
        $entity = $this->getExample();
        $entity->scope['id'] = 456;
        $this->assertEquals(456, $entity->getScopeId());
        $entity->status = 'pending';
        $this->assertEquals(123, $entity->getScopeId());
    }

    public function testMerge()
    {
        $example = $this->getExample();
        $example->addData(['scope' => ['shortName' => 'Test']]);
        $this->assertEquals($example['scope']['shortName'], 'Test');
        $this->assertTrue($example->scope instanceof \BO\Zmsentities\Scope);
        $this->assertTrue($example->testValid());
    }

    public function testCreateFromScope()
    {
        $scope = new \BO\Zmsentities\Scope(['id' => '789']);
        $process = \BO\Zmsentities\Process::createFromScope($scope, new \DateTime('2016-04-01 12:00:00'));
        $this->assertEquals('queued', $process->status);
        $this->assertEquals('00:00', $process->getFirstAppointment()->getStartTime()->format('H:i'));
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
        $entity->scope->dayoff = (new \BO\Zmsentities\Collection\DayoffList())->addEntity($dayoff);
        $collection->addEntity($entity);
        $collection = $collection->withLessData();
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertFalse(isset($entity->withLessData()['dayoff']), 'Converting to less data failed');
    }

    public function testAvailability()
    {
        $availability = new Availability([
            'id' => '93181',
            'weekday' => array(
                'monday' => '0',
                'tuesday' => '4',
                'wednesday' => '0',
                'thursday' => '0',
                'friday' => '0',
                'saturday' => '0',
                'sunday' => '0'
            ),
            'repeat' => array(
                'afterWeeks' => '1',
            ),
            'workstationCount' => array(
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
