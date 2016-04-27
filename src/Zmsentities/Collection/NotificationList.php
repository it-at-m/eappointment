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
}
