<?php
namespace BO\Zmsapi\Notification;

/**
 *
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *            A class with a structure of an ics-appointment
 */
class Base extends \ArrayObject
{
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
