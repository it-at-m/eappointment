<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Notification as Entity;
use \BO\Zmsentities\Collection\NotificationList as Collection;

class Mail extends Base
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
            $notificationList = new Collection($result);
        }
        return $notificationList;
    }

    public function writeInQueue(Entity $notification)
    {
        $query = new Query\Notification(Query\Base::INSERT);
        $query->addValues(array(
            'processID' => $notification->process['id'],
            'departmentID' => $notification->department['id'],
            'createIP' => $notification->createIP,
            'createTimestamp' => time(),
            'subject' => $notification->subject,
        ));
        $result = $this->writeItem($query);
        if ($result) {
            $itemId = $this->getWriter()->lastInsertId();
            $notification = $this->readEntity($itemId);
            $status = true;
        } else {
            return false;
        }
        return ($status) ? $notification : null;
    }

    public function deleteEntity($itemId)
    {
        $query =  new Query\Notification(Query\Base::DELETE);
        $query->addConditionItemId($itemId);
        return $this->deleteItem($query);
    }
}
