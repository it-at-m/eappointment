<?php
namespace BO\Zmsentities\Collection;

class NotificationList extends Base
{

    public function addNotification($notification)
    {
        if ($notification instanceof \BO\Zmsentities\Notification) {
            $this[] = clone $notification;
        }

        return $this;
    }

    public function hasNotification($itemId)
    {
        foreach ($this as $notification) {
            if ($notification->id == $itemId) {
                return true;
            }
        }
        return false;
    }
}
