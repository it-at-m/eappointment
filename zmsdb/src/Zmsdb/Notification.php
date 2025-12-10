<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Notification as Entity;
use BO\Zmsentities\Collection\NotificationList as Collection;

class Notification extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Mail
     */
    public function readEntity($itemId, $resolveReferences = 1)
    {
        $query = new Query\Notification(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionItemId($itemId);
        $notification = $this->fetchOne($query, new Entity());
        if ($notification && $notification->hasId()) {
            $notification = $this->readResolvedReferences($notification, $resolveReferences);
        }
        return $notification;
    }

    public function readList($resolveReferences = 1)
    {
        $notificationList = new Collection();
        $query = new Query\Notification(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $item) {
                $entity = new Entity($item);
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                if ($entity instanceof Entity) {
                    $notificationList->addEntity($entity);
                }
            }
        }
        return $notificationList;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $notification, $resolveReferences)
    {
        if (1 <= $resolveReferences) {
            $processQuery = new \BO\Zmsdb\Process();
            $process = $processQuery
                ->readEntity(
                    $notification->process['id'],
                    new Helper\NoAuth(),
                    $resolveReferences - 1
                );
            // only overwrite process with resolved version if not dereferenced
            if ($process && $notification->getScopeId() == $process->getScopeId()) {
                $notification->process = $process;
            }
            $notification->department = (new \BO\Zmsdb\Department())
                ->readEntity($notification->department['id'], $resolveReferences - 1);
        }
        return $notification;
    }


    public function writeInQueue(Entity $notification, \DateTimeInterface $dateTime, $count = true)
    {
        $queueId = null;
        $process = new \BO\Zmsentities\Process($notification->process);
        $client = $process->getFirstClient();
        if (! $client->hasTelephone()) {
            throw new Exception\Notification\ClientWithoutTelephone();
        }
        $notification->hasProperties('message', 'process');
        $telephone = preg_replace('/\s+/', '', $client->telephone);
        $department = (new Department())->readByScopeId($process->getScopeId(), 0);
        $query = new Query\Notification(Query\Base::INSERT);
        $query->addValues(array(
            'processID' => $notification->process['id'],
            'scopeID' => $notification->process['scope']['id'],
            'departmentID' => $department->toProperty()->id->get(),
            'createIP' => $notification->createIP,
            'createTimestamp' => time(),
            'message' => $notification->message,
            'clientFamilyName' => $client->familyName,
            'clientTelephone' => $telephone,
        ));
        $success = $this->writeItem($query);
        $queueId = $this->getWriter()->lastInsertId();
        if ($count && $success) {
            $client->notificationsSendCount += 1;
            (new Process())->updateEntity($process, $dateTime);
        }
        return $this->readEntity($queueId);
    }

    public function writeInCalculationTable(\BO\Zmsentities\Schema\Entity $notification)
    {
        $amount = ceil((strlen(trim($notification->message))) / 160);
        $scopeId = $notification->getScopeId();
        if (!$scopeId) {
            return false;
        }
        $client = $notification->getClient();
        $query = Query\Notification::QUERY_WRITE_IN_CALCULATION;
        return $this->perform($query, array(
            $scopeId,
            $client->telephone,
            $notification->getCreateDateTime()->format('Y-m-d'),
            $amount
        ));
    }

    public function deleteEntity($itemId)
    {
        $query =  new Query\Notification(Query\Base::DELETE);
        $query->addConditionItemId($itemId);
        return $this->deleteItem($query);
    }
}
