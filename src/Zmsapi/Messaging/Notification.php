<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsapi\Messaging;

class Notification extends Base
{

    /*
     * TODO - Um das ICS Attachment zu holen gibt es den API aufruf der auch funktioniert.
     * Nun muss hier aber der Content abgefragt werden der für die entsprechende Mail vorgesehen wird.
     * Soll dieser ebenfalls aus der API / Notification Folder geholt werden?
     * Hier gibt es schon eine Funktion (createConfirmMessage()) die den Text erstellt. Aber es bedarf doch einer
     * neuen API Route oder? Beispiel (/process/id/authKey/mail/{confirm,reminder,info}
     *
     * */
    public static function getEntityData(\BO\Zmsentities\Process $process)
    {
        $entity = new \BO\Zmsentities\Notification();
        $entity->process['id'] = $process->id;
        $entity->message = self::createNotificationMessage($process);
        $entity->createIP = $process->createIP;
        $entity->department['id'] = $process->getDepartmentId();
        $entity->process['scope']['id'] = $process->getScopeId();
        return $entity;
    }
}
