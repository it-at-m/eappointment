<?php

namespace BO\Zmsentities\Tests;

use \BO\Mellon\Condition;
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
        $this->assertStringContainsString('122305', $entity->getRequestCSV(), 'requests are not accessible');
        $entity->addAmendment(array('amendment' => 'Das ist ein Zusatztext'));
        $entity->addScope('141');
        $this->assertTrue('141' == $entity->getScopeId(), 'scope id is not accessible');
        $this->assertTrue('141' == $entity->getCurrentScope()->getId(), 'current scope is not accessible');
        $entity->setRandomAuthKey();
        $this->assertTrue(4 == strlen($entity->getAuthKey()), 'set random authKey failed');
        $this->assertEquals('Das ist ein Zusatztext', $entity->getAmendment());

        $entity->setStatus('reserved');
        $this->assertTrue('reserved' == $entity->getStatus(), 'status is not accessible');

        $this->assertTrue(1447931730 == $entity->getReminderTimestamp(), 'reminder timestamp is not set');

        $this->assertStringContainsString('122305', (string)$entity, 'requests are not accessible');

        $this->assertTrue(2 == count($entity->getRequestIds()), 'requests are not accessible');
        $this->assertStringContainsString('122305', $entity->getRequestCsv(), 'requests are not accessible');

        $this->assertFalse($entity->hasScopeAdmin());
    }

    public function testAddAppointmentFromRequest()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $appointment = $entity->addAppointmentFromRequest(
            [
                'selecteddate' => '2016-04-01',
                'selectedtime' => '11-51-00',
                'slotCount' => 3
            ],
            $now
        )->getFirstAppointment();
        $this->assertEquals('2016-04-01 11:51', $appointment->toDateTime()->format('Y-m-d H:i'));
        $this->assertEquals(3, $appointment->slotCount);
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
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $entity = $this->getExample();
        $entity->appointments = array();
        $firstAppointment = $entity->getFirstAppointment();
        $this->assertTrue($firstAppointment instanceof \BO\Zmsentities\Appointment);
        $entity->appointments = array();
        $appointment = (new \BO\Zmsentities\Appointment())->getExample();
        $appointment->date = $now->getTimestamp();
        $entity->addAppointment($appointment);
        $this->assertTrue($entity->hasArrivalTime());
        $this->assertTrue($entity->hasAppointment(1451649000, 0), 'appointment is not accessible');
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');

        $appointment = (new \BO\Zmsentities\Appointment())->getExample()->getArrayCopy();
        $entity->appointments = array($appointment);
        $this->assertFalse($entity->hasAppointment(1447869173, 123), 'appointment date 1447869173 should not exist');
    }

    public function testWithReassignedCredentials()
    {
        $entity = $this->getExample();
        $entity2 = $this->getExample();
        $entity2->id = 987654;
        $entity2->authKey = 'dcba';

        $entity->withReassignedCredentials($entity2);
        
        $this->assertTrue($entity->getId() == $entity2->getId());
        $this->assertTrue($entity->getAuthKey() == $entity2->getAuthKey());
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
        $entity->queue['callCount'] = 4;
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
        $this->assertFalse($entity->getFirstClient()->toProperty()->familyName->isAvailable());
        $this->assertFalse($entity->getFirstClient()->toProperty()->email->isAvailable());
    }

    public function testGetWaitedSeconds()
    {
        $entity = $this->getExample();
        $seconds = $entity->getWaitedSeconds();
        $this->assertEquals(53767, $seconds);
        $entity->queue->withAppointment = 0;
        $entity->getFirstAppointment()->setDateByString('2016-04-01 00:00');
        $seconds = $entity->getWaitedSeconds();
        $this->assertEquals(45, $seconds);

        $now = new \DateTimeImmutable();
        $now = $now->setTimestamp($entity->queue['callTime']);
        $entity->queue['callTime'] = null;
        $this->assertEquals(45, $entity->getWaitedSeconds($now));
    }

    public function testGetWaitedMinutes()
    {
        $entity = $this->getExample();
        $minutes = $entity->getWaitedMinutes();
        $this->assertEquals(896.1167, round($minutes, 4));
        $entity->queue->withAppointment = 0;
        $entity->getFirstAppointment()->setDateByString('2016-04-01 00:00');
        $minutes = $entity->getWaitedMinutes();
        $this->assertEquals(0.75, $minutes);

        $now = new \DateTimeImmutable();
        $now = $now->setTimestamp($entity->queue['callTime']);
        $entity->queue['callTime'] = null;
        $this->assertEquals(0.75, $entity->getWaitedMinutes($now));
    }

    public function testToDereferencedAmendment()
    {
        $entity = $this->getExample();
        $amendment = $entity->toDerefencedAmendment();
        $this->assertStringContainsString("LastChange' => '2015-11-19T12:13:16+01:00'", $amendment);
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
            'surveyAccepted' => 1,
            'sendConfirmation' => 1,
            'sendMailConfirmation' => 1,
            'sendReminder' => 1,
            'amendment' => 'Test Zusatz',
            'customTextfield' => 'Test Zusatz zwei',
            'requests' => [120335,120697]
        ]);
        $validator->makeInstance();

        //$formCollection = $this->getFormValidator($entity, $validator);
        //$this->assertFalse($formCollection['failed']);
        
        $input = [
            'headsUpTime' => 60,
            'slotCount' => 1,
            'requests' => [120335,120697]
        ];

        $scope = (new \BO\Zmsentities\Scope)->getExample();
        $entity->withUpdatedData($input, $now, $scope);
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
            'customTextfield' => 'Test Zusatz zwei',
            'agbgelesen' => 1
        ]);
        $validator->makeInstance();
        $formCollection = $this->getFormValidator($entity, $validator);
        $entity->addClientFromForm($formCollection);
        $this->assertEquals('Max Mustermann', $entity->getClients()->getFirst()->familyName);
        //$this->assertEquals('zms@berlinonline.de', $entity->getClients()->getFirst()->email); Domain error
    }

    public function testFromManageProcess()
    {
        $entity = $this->getExample();
        $validator = new \BO\Mellon\Validator([
            'process' => 100029,
            'authKey' => '1c56'
        ]);
        $validator->makeInstance();
        $formCollection = $this->getFormValidatorManageProcess($entity, $validator);
        $this->assertFalse($formCollection['process']['failed']);
        $this->assertFalse($formCollection['authKey']['failed']);
    }

    public function testFromManageProcessFailed()
    {
        $entity = $this->getExample();
        $validator = new \BO\Mellon\Validator([
            'process' => 10029,
            'authKey' => ''
        ]);
        $validator->makeInstance();
        $formCollection = $this->getFormValidatorManageProcess($entity, $validator);
        $this->assertTrue($formCollection['process']['failed']);
        $this->assertTrue($formCollection['authKey']['failed']);
        $this->assertEquals(
            'Eine Vorgangsnummer besteht aus mindestens 6 Ziffern',
            $formCollection['process']['messages'][0]
        );
        $this->assertEquals(
            'Es müssen mindestens 4 Zeichen eingegeben werden.',
            $formCollection['authKey']['messages'][0]
        );
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
            'customTextfield' => 'Test Zusatz zwei',
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
        $this->assertArrayHasKey(
            '1447869171', (array) $processListByTime->sortByTimeKey(),
            'Failed to create process list by time'
        );
        $this->assertEquals(123456, $collection->getFirst()->id, 'First process not found in process list');
        $this->assertEquals(2, count($collection->getAppointmentList()));

        $queueList = $collection->toQueueList($now);
        $this->assertEquals('queued', $queueList->getFirst()->status);
        $this->assertEquals('1447869171', $queueList->getFirst()->arrivalTime);

        $entity3 = $this->getExample();
        $entity3->queue['waitingTimeEstimate'] = 60;
        $entity3->getFirstClient()['familyName'] = 'Anton Beta';
        $collection->addEntity($entity3);
        $this->assertEquals('Anton Beta', $collection->sortByClientName()->getFirst()->getFirstClient()['familyName']);

        $this->assertEquals(1, $collection->getRequestList()->count());
        $this->assertEquals(120335, $collection->getRequestList()->getFirst()->getId());

        $this->assertEquals('0', $collection->sortByEstimatedWaitingTime()->getFirst()->queue['waitingTimeEstimate']);
        $this->assertEquals('60', $collection->sortByEstimatedWaitingTime()->getLast()->queue['waitingTimeEstimate']);
    }

    public function testSetTempAppointmentToProcess()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new $this->collectionclass();
        $collection->setTempAppointmentToProcess($now, '999');
        $collection->setTempAppointmentToProcess($now, '999');
        $this->assertEquals(999, $collection->getFirst()->getFirstAppointment()->getScope()->getId());
    }

    public function testProcessListSortedByAppointmentDate()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);

        $entity2 = $this->getExample();
        $entity2->getFirstAppointment()->date = 1459511423;
        $collection->addEntity($entity2);
        $collection->sortByAppointmentDate();
        $this->assertTrue(
            $collection->getFirst()->getFirstAppointment()->date < $collection->getLast()->getFirstAppointment()->date
        );
    }

    public function testProcessListByRequest()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);

        $entity2 = $this->getExample();
        $entity2->requests = new \BO\Zmsentities\Collection\RequestList();
        $collection->addEntity($entity2);
        $list = $collection->withRequest(120335);
        $this->assertCount(1, $list);
    }


    public function testScopeList()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $scopeList = $collection->getScopeList();
        $this->assertEquals(1, $scopeList->count());

        $entity2 = clone $entity;
        $entity2->appointments = (new \BO\Zmsentities\Collection\AppointmentList())
            ->addEntity((new \BO\Zmsentities\Appointment())->getExample()->setTime('18:30'));
        $collection->addEntity($entity2);
        $this->assertEquals(2, $collection->count());
        $this->assertEquals(2, $collection->withUniqueScope()->count());
        $this->assertEquals(1, $collection->withUniqueScope(true)->count());
    }

    public function testWithScopeId()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $collection->addEntity($entity);
        $entity2 = $this->getExample();
        $entity2->scope['id'] = 141;
        $entity2->getFirstAppointment()->getScope()->id = 141;
        $collection->addEntity($entity2);
        $this->assertEquals(1, $collection->withScopeId(123)->count());
        $this->assertEquals(1, $collection->withOutScopeId(141)->count());
    }

    public function testWithScopeIdForPickupWithDifferentScopeId()
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

    public function testToConflictListByDay()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $entity2 = $this->getExample();
        $availability = (new \BO\Zmsentities\Availability())->getExample();
        $availability->id = "1";
        $availability2 = (new \BO\Zmsentities\Availability())->getExample();
        $availability2->id = "2";
        $entity->getFirstAppointment()->availability = $availability;
        $entity2->getFirstAppointment()->availability = $availability2;
        $collection->addEntity($entity);
        $collection->addEntity($entity2);
        $list = $collection->toConflictListByDay();
        $this->assertArrayHasKey('2015-11-18', $list);
        //$this->assertEquals('Beispiel Termin', $list['2015-11-18'][0]['message']);
        $this->assertEquals('18:52', $list['2015-11-18'][0]['appointments'][0]['startTime']);
        // endTime = slotTimeInMinutes * slotCount 12X2 = 24
        $this->assertEquals('19:16', $list['2015-11-18'][0]['appointments'][0]['endTime']);
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

        $this->assertTrue($withoutAppointment->hasArrivalTime());
        $this->assertTrue($queueWith->withAppointment);
        $this->assertFalse($queueWithout->withAppointment);
    }

    public function testPickup()
    {
        $entity = $this->getExample();
        $entity->scope['id'] = 456;
        $this->assertEquals(456, $entity->getScopeId());
        $entity->status = 'pending';
        $this->assertEquals(456, $entity->getScopeId());
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
        $appointment->scope['id'] = 999;
        $entity->appointments = (new \BO\Zmsentities\Collection\AppointmentList())->addEntity($appointment);
        $entity->scope = (new \BO\Zmsentities\Scope())->getExample();
        $dayoff = (new \BO\Zmsentities\Dayoff())->getExample();
        $entity->scope->dayoff = (new \BO\Zmsentities\Collection\DayoffList())->addEntity($dayoff);
        $entity->scope->provider['data'] = array();
        $entityFree = clone $entity;
        $entityFree->status = 'free';
        $collection->addEntity($entity);
        $collection->addEntity($entityFree);
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
                        'date' => strtotime('2016-04-19 11:15'),
                        'slotCount' => 1
                    ])
                ],
                'status' => 'confirmed',
            ]),
            new Process([
                'id' => '2',
                'appointments' => [
                    new Appointment([
                        'date' => strtotime('2016-04-19 12:15'),
                        'slotCount' => 1,
                        'availability' => $availability
                    ])
                ],
                'status' => 'confirmed',
            ]),
            new Process([
                'id' => '3',
                'appointments' => [
                    new Appointment([
                        'date' => strtotime('2016-04-19 12:15'),
                        'slotCount' => 2,
                        'availability' => $availability
                    ])
                ],
                'status' => 'confirmed',
            ]),
        ]);
        $withAvailability = $processList->withAvailability($availability)->setConflictAmendment();
        $withStrict = $processList->withAvailabilityStrict($availability);
        $withOutAvailability = $processList->withOutAvailability($availabilityList)->setConflictAmendment();
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
        $this->assertStringContainsString(
            'Die Slots für diesen Zeitraum wurden überbucht',
            $withAvailability->getLast()->amendment
        );
        $this->assertStringContainsString(
            'Der Vorgang (1) befindet sich außerhalb der Öffnungszeit!',
            $withOutAvailability->getFirst()->amendment
        );
    }

    public function testWithReference()
    {
        $entity = $this->getExample();
        $entity = $entity->withResolveLevel(0);
        $this->assertArrayHasKey('$ref', (array) $entity->requests->getFirst());
    }

    public function getFormValidatorManageProcess($process, $validator)
    {
        $processValidator = new \BO\Zmsentities\Validator\ProcessValidator($process);
        $delegatedProcess = $processValidator->getDelegatedProcess();
        $processValidator
            ->validateId(
                $validator->getParameter("process"),
                $delegatedProcess->setter('id'),
                function () {
                    return true;
                }
            )
            ->validateAuthKey(
                $validator->getParameter('authKey'),
                $delegatedProcess->setter('authKey'),
                function () {
                    return true;
                }
            );
        $form = $processValidator->getCollection()->getStatus(null, true);
        $form['failed'] = $processValidator->getCollection()->hasFailed();
        return $form;
    }

    protected function getFormValidator($process, $validator)
    {
        $processValidator = new \BO\Zmsentities\Validator\ProcessValidator($process);
        $delegatedProcess = $processValidator->getDelegatedProcess();
        $processValidator
            ->validateName(
                $validator->getParameter('familyName'),
                $delegatedProcess->setter('clients', 0, 'familyName')
            )
            ->validateRequests(
                $validator->getParameter('requests'),
                $delegatedProcess->setter('requests')
            )
            ->validateMail(
                $validator->getParameter('email'),
                $delegatedProcess->setter('clients', 0, 'email'),
                new Condition(
                    $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1),
                    $validator->getParameter('surveyAccepted')->isString()->isDevoidOf([1])
                )
            )
            ->validateTelephone(
                $validator->getParameter('telephone'),
                $delegatedProcess->setter('clients', 0, 'telephone'),
                new Condition(
                    $validator->getParameter('sendConfirmation')->isNumber()->isNotEqualTo(1),
                    $validator->getParameter('sendReminder')->isNumber()->isNotEqualTo(1)
                )
            )
            ->validateSurvey(
                $validator->getParameter('surveyAccepted'),
                $delegatedProcess->setter('clients', 0, 'surveyAccepted')
            )
            ->validateText(
                $validator->getParameter('amendment'),
                $delegatedProcess->setter('amendment')
            )
            ->validateReminderTimestamp(
                $validator->getParameter('headsUpTime'),
                $delegatedProcess->setter('reminderTimestamp'),
                new Condition(
                    $validator->getParameter('sendReminder')->isNumber()->isNotEqualTo(1)
                )
            )
            
        ;
        $processValidator->getCollection()->addValid(
            $validator->getParameter('sendConfirmation')->isNumber(),
            $validator->getParameter('sendReminder')->isNumber()
        );

        $form = $processValidator->getCollection()->getStatus(null, true);
        $form['failed'] = $processValidator->getCollection()->hasFailed();
        return $form;
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
