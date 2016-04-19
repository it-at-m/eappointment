<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsapi\Notification;

class Mail extends Base
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
        $entity = new \BO\Zmsentities\Mail();
        $entity->process['id'] = $process->id;
        $entity->subject = self::createSubject($process);
        $entity->createIP = $process->createIP;
        $entity->department['id'] = $process['scope']['department']['id'];
        $entity->multipart[] = array(
            'mime' => 'text/html',
            'content' => self::createMessage($process),
            'base64' => true,
        );
        return $entity;
    }

    protected static function createMessage($process)
    {
        $appointment = $process->getFirstAppointment();
        $template = $process->status . 'Message.twig';
        ob_start();
        \BO\Slim\Render::html(
            'notification/'. $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => \BO\Zmsdb\Config::readEntity()
            )
        );
        $message = ob_get_contents();
        ob_end_clean();
        return \base64_encode($message);
    }

    protected static function createSubject($process)
    {
        $appointment = $process->getFirstAppointment();
        $template = 'subjects.twig';
        ob_start();
        \BO\Slim\Render::html(
            'notification/'. $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => \BO\Zmsdb\Config::readEntity()
            )
        );
        $subject = ob_get_contents();
        $subject = trim($subject);
        ob_end_clean();
        return $subject;
    }
}
