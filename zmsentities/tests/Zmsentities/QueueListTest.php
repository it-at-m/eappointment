<?php

namespace BO\Zmsentities\Tests;

class QueueListTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2016-11-19 08:50:00';

    const FAKE_WAITINGNUMBER = 1002;

    public $entityclass = '\BO\Zmsentities\Queue';

    public $collectionclass = '\BO\Zmsentities\Collection\QueueList';

    public function testBasic()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);

        $withAppointment = (new $this->entityclass())->getExample();
        $withoutAppointment = (clone $withAppointment);
        $withAppointment->withAppointment = true;
        $withAppointment->status = 'called';
        $withAppointment->number = '122';

        $collection = new $this->collectionclass();
        $collection->addEntity($withAppointment);
        $collection->addEntity($withoutAppointment);
        $collection->withSortedArrival();

        $this->assertEquals(1, $collection->withAppointment()->count());
        $this->assertEquals(1, $collection->withOutAppointment()->count());

        $this->assertEquals(1, $collection->withStatus(['called'])->count());
        $this->assertEquals(0, $collection->withoutStatus(['called','confirmed'])->count());

        $withWaitingTime = $collection->withEstimatedWaitingTime(10, 1, $now);
        $estimatedWaitingData = $withWaitingTime->getEstimatedWaitingTime(10, 1, $now);
        $this->assertEquals(
            $estimatedWaitingData['waitingTimeEstimate'],
            $withWaitingTime->getFakeOrLastWaitingnumber()->waitingTimeEstimate
        );
        $this->assertEquals(20, $withWaitingTime->getFakeOrLastWaitingnumber()->waitingTimeEstimate);
        $this->assertEquals(8, $withWaitingTime->getFakeOrLastWaitingnumber()->waitingTimeOptimistic);

        $withWaitingTime = $collection->withEstimatedWaitingTime(10, 2, $now);
        $estimatedWaitingData = $withWaitingTime->getEstimatedWaitingTime(10, 2, $now);
        $this->assertEquals(10, $withWaitingTime->getFakeOrLastWaitingnumber()->waitingTimeEstimate);
        $this->assertEquals(0, $withWaitingTime->getFakeOrLastWaitingnumber()->waitingTimeOptimistic);
        $this->assertEquals(2, $estimatedWaitingData['amountBefore']);

        $this->assertEquals(null, $collection->getQueueByNumber(999));
        $this->assertEquals(null, $collection->getQueuePositionByNumber(999));
    }

    public function testGetLastWaitingNumber()
    {
        $withAppointment = (new $this->entityclass())->getExample();
        $withoutAppointment = (clone $withAppointment);
        $withAppointment->withAppointment = true;
        $collection = new $this->collectionclass();
        $collection->addEntity($withAppointment);
        $collection->addEntity($withoutAppointment);
        $this->assertEquals(123, $collection->getFakeOrLastWaitingnumber()->number);
    }

    public function testWithEstimatedWaitingTimeFailed()
    {
        $this->expectException('\Exception');
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new $this->collectionclass();
        $collection->withEstimatedWaitingTime(0, 1, $now);
    }

    public function testGetNextProcess()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new \BO\Zmsentities\Collection\ProcessList();
        $process = (new \BO\Zmsentities\Process)->getExample();
        $collection->addEntity($process);
        $queueList = $collection->toQueueList($now)->withoutDublicates();
        $this->assertEntity('\BO\Zmsentities\Process', $queueList->getNextProcess($now));

        $process2 = clone $process;
        $process2->id = 999999;
        $process2->getFirstAppointment()->date = 1396344003;
        $collection->addEntity($process2);
        $queueList = $collection->toQueueList($now);
        $nextProcess = $queueList->getNextProcess($now, '999999');
        $this->assertEquals(123456, $nextProcess->id);

        $nextProcess = $queueList->getNextProcess($now, '999999,123456');
        $this->assertEquals(null, $nextProcess);
    }

    public function testSetProcess()
    {
        $entity = $this->getExample();
        $process = (new \BO\Zmsentities\Process)->getExample();
        $entity->setProcess($process);
        $this->assertEquals(123456, $entity->getProcess()->id);
    }

    public function testToProcessList()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new \BO\Zmsentities\Collection\ProcessList();
        $process = (new \BO\Zmsentities\Process)->getExample();
        $collection->addEntity($process);
        $queueList = $collection->toQueueList($now);
        $this->assertEntityList('\BO\Zmsentities\Queue', $queueList);
        $processList = $queueList->toProcessList();
        $this->assertEntityList('\BO\Zmsentities\Process', $processList);
    }

    public function testGetWaitingNumberList()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new \BO\Zmsentities\Collection\ProcessList();
        $process = (new \BO\Zmsentities\Process)->getExample();
        $collection->addEntity($process);
        $queueList = $collection->toQueueList($now);
        $this->assertEquals('123456', $queueList->getWaitingNumberListCsv());
    }

    public function testWithSortedWaitingTime()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $collection = new \BO\Zmsentities\Collection\ProcessList();
        $process = (new \BO\Zmsentities\Process)->getExample();
        $process2 = (new \BO\Zmsentities\Process)->getExample();
        $process->queue->arrivalTime = $now->getTimestamp();
        $process2->queue->arrivalTime = $now->modify('+20 minutes')->getTimestamp();
        $process->getFirstAppointment()->date = $process->queue->arrivalTime;
        $process2->getFirstAppointment()->date = $process2->queue->arrivalTime;
        $process->queue->callTime = 0;
        $process2->queue->callTime = 0;
        $collection->addEntity($process);
        $collection->addEntity($process2);
        $queueList = $collection->toQueueList($now)->withEstimatedWaitingTime(10, 2, $now)->withSortedWaitingTime();
        $this->assertEquals(5, $queueList->getFirst()->waitingTimeEstimate);
        $this->assertEquals(10, $queueList[1]->waitingTimeEstimate);
        $this->assertEquals(23, $queueList->getLast()->waitingTimeEstimate);

        $this->assertEquals(2, $queueList->withoutStatus(['fake'])->getCountWithWaitingTime()->count());
    }

    public function testSetProcessFailed()
    {
        $entity = $this->getExample();
        $entity->process = (new \BO\Zmsentities\Process)->getExample()->getArrayCopy();
        $this->assertEquals(null, $entity->getProcess());
    }

    public function testDestinationManipulation()
    {
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $cluster = (new \BO\Zmsentities\Cluster())->getExample();
        $entity = (new $this->entityclass())->getExample();
        $collection = new $this->collectionclass();
        $collection->addEntity($entity);

        $withPickup = $collection->withPickupDestination($scope);
        $this->assertEquals("Ausgabe von Dokumenten", $withPickup->getFirst()->destination);

        $withShortName = $collection->withShortNameDestinationHint($cluster, $scope);
        $this->assertEquals("Zentrale", $withShortName->getFirst()->destinationHint);
    }
}
