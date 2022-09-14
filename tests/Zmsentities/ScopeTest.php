<?php

namespace BO\Zmsentities\Tests;

class ScopeTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-04-01 11:50:00';
    const LAST_GIVEN_NUMBER_TIME = '2015-11-19 10:25:59';

    public $entityclass = '\BO\Zmsentities\Scope';

    public $collectionclass = '\BO\Zmsentities\Collection\ScopeList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(is_array($entity->getNotificationPreferences()), 'Notification preferences not available');
        $this->assertStringContainsString('erfolgreich', $entity->getConfirmationContent(), 'Confirmation content not available');
        $this->assertStringContainsString('Warteraum', $entity->getHeadsUpContent(), 'Confirmation HeadsUpContent not available');
        $this->assertTrue('23' == $entity->getStatus('queue', 'givenNumberCount'), 'Status is not accessible');
        $this->assertTrue(null === $entity->getContactEmail(), 'Contact eMail should not be available');
        $this->assertStringContainsString('Flughafen', $entity->getName(), 'Contact name not available');
        $this->assertEquals('Bürgeramt', $entity->getScopeInfo(), 'Scope Info is not available');
        $this->assertEquals('dritte Tür rechts', $entity->getScopeHint(), 'Scope hint (from hint) is not available');
        $this->assertStringContainsString('Flughafen', (string)$entity, 'Contact name not available');
        $this->assertFalse($entity->hasEmailFrom());
        $this->assertTrue($entity->hasNotificationEnabled());
    }

    public function testWithCleanedUpFormData()
    {
        $entity = $this->getExample();
        $entity->removeImage = 1;
        $entity->save = 'submit';
        $this->assertArrayNotHasKey('save', (array) $entity->withCleanedUpFormData());
    }

    public function testProvider()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals('123456', $entity->getProviderId(), 'ProviderId does not exists');
        $entity->provider = array('$ref' => '/provider/dldb/123456/');
        $this->assertTrue('123456' == $entity->getProviderId(), 'ProviderId does not exists');
        $this->assertEquals('dldb', $entity->getProvider()->source, 'ProviderId does not exists');
        $entity->provider = null;
        try {
            $entity->getProviderId();
            $this->fail("Expected exception ScopeMissingProvider not thrown");
        } catch (\BO\Zmsentities\Exception\ScopeMissingProvider $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testGetPreferences()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue('3' == $entity->getPreference('queue', 'callCountMax'), 'string preference not available');
        $this->assertTrue('1' == $entity->getPreference('survey', 'enabled', true), 'bool preference not available');
    }

    public function testScopeList()
    {
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $newCollection = new $this->collectionclass();
        $collection->addEntity($entity);
        $newCollection->addScopeList($collection);
        $this->assertTrue($newCollection->hasEntity($entity->id), 'Failed to add scopelist to another list');

        $this->assertEquals(1, $newCollection->getProviderList()->count());
    }

    public function testCalculatedWorkstationCount()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals(1, $entity->getCalculatedWorkstationCount());
        $entity->status['queue']['workstationCount'] = 0;
        $entity->status['queue']['ghostWorkstationCount'] = 2;
        $this->assertEquals(2, $entity->getCalculatedWorkstationCount());
    }

    public function testSetStatusAvailability()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->setStatusAvailability('isOpened', true)->getStatus('availability', 'isOpened'));
    }

    public function testGetBookableEndDate()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);

        //with endInDays from scope
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals('2016-05-31', $entity->getBookableEndDate($now)->format('Y-m-d'));

        //without endInDays from scope
        $entity = (new $this->entityclass())->getExample();
        unset($entity->preferences['appointment']['endInDaysDefault']);
        $this->assertEquals('2016-04-01', $entity->getBookableEndDate($now)->format('Y-m-d'));
    }

    public function testGetWaitingTimeFromQueueList()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);

        $queue = (new \BO\Zmsentities\Queue())->getExample();
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        $queueList->addEntity($queue);

        $scope = (new $this->entityclass())->getExample();
        $queueEstimatedData = $scope->getWaitingTimeFromQueueList($queueList, $now);
        $this->assertEquals(
            $scope->getPreference('queue', 'processingTimeAverage') * 2,
            $queueEstimatedData['waitingTimeEstimate']
        );
    }

    public function testUpdateStatusQueue()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $lastGivenNumberTime = new \DateTimeImmutable(self::LAST_GIVEN_NUMBER_TIME);
        $entity = (new $this->entityclass())->getExample();
        $entity->status['queue']['lastGivenNumber'] = 0;
        $this->assertEquals(23, $entity->toProperty()->status->queue->givenNumberCount->get());
        $updatedEntity = $entity->updateStatusQueue($lastGivenNumberTime);
        $this->assertEquals(24, $entity->toProperty()->status->queue->givenNumberCount->get());
        $updatedEntity = $entity->updateStatusQueue($now);
        $this->assertEquals(1, $entity->toProperty()->status->queue->givenNumberCount->get());

        $entity->status['queue']['lastGivenNumber'] = 501;
        $updatedEntity = $entity->updateStatusQueue($now);
        $this->assertEquals(300, $updatedEntity->toProperty()->status->queue->lastGivenNumber->get());
    }

    public function testGetDayoffList()
    {
        $entity = $this->getExample();
        $entity->dayoff[] = [
            "date" => 1447922381000,
            "name" => "TestAsArray"
        ];
        $this->assertEquals(1, $entity->getDayoffList()->count());
        $this->assertEntityList('\BO\Zmsentities\Dayoff', $entity->getDayoffList());
        $this->assertArrayNotHasKey('dayoff', (array) $entity->withLessData());
    }

    public function testNewerThan()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = $this->getExample();
        $entity->lastChange = $now->getTimestamp();
        $this->assertTrue($entity->isNewerThan($now->modify('-1 day')));
        $this->assertFalse($entity->isNewerThan($now->modify('+1 day')));
    }

    public function testRequestList()
    {
        $entity = $this->getExample();
        $entity->provider['data']['services'] = array(array('service' => '1234'));
        $requestList = $entity->getRequestList();
        $this->assertTrue($requestList->hasRequests(1234), "Missing request in provider from scope");
    }
}
