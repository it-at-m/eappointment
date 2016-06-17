<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Notification as Entity;
use \BO\Zmsentities\Collection\NotificationList as Collection;

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
        return $this->fetchOne($query, new Entity());
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
            foreach ($result as $notification) {
                $scope = (new Scope())->readEntity($notification->getScopeId(), $resolveReferences);
                $notification->addScope($scope);
                $notificationList->addEntity($notification);
            }
            $notificationList = new Collection($result);
        }
        return $notificationList;
    }

    public function writeInQueue(Entity $notification)
    {
        $process = new \BO\Zmsentities\Process($notification->process);
        $department = new \BO\Zmsentities\Department($notification->department);
        $client = $process->getFirstClient();
        if (!$client->hasTelephone() || !$department->hasNotificationEnabled()) {
            return false;
        }
        $query = new Query\Notification(Query\Base::INSERT);
        $query->addValues(array(
            'processID' => $notification->process['id'],
            'departmentID' => $notification->department['id'],
            'createIP' => $notification->createIP,
            'createTimestamp' => time(),
            'message' => $notification->message,
            'clientFamilyName' => $client->familyName,
            'clientTelephone' => $client->telephone,
        ));
        $result = $this->writeItem($query);
        if ($result) {
            $queueId = $this->getWriter()->lastInsertId();
            $this->updateProcess($notification);
        } else {
            return false;
        }
        return $queueId;
    }

    public function deleteEntity($itemId)
    {
        $query =  new Query\Notification(Query\Base::DELETE);
        $query->addConditionItemId($itemId);
        return $this->deleteItem($query);
    }

    public function deleteEntityByProcess($processId)
    {
        $query = Query\Notification::QUERY_DELETE_BY_PROCESS;
        $statement = $this->getWriter()->prepare($query);
        return $statement->execute(array($processId));
    }

    private function updateProcess(\BO\Zmsentities\Notification $notification)
    {
        $query = new Process();
        $process = $query->readEntity($notification->getProcessId(), $notification->getProcessAuthKey());
        //error_log(var_export($process,1));
        $client = $process->getFirstClient();
        $client->notificationsSendCount += 1;
        //update process
        $process->updateClients($client);
        $query->updateEntity($process);
    }
}
