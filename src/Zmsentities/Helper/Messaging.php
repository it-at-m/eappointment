<?php
/**
 *
 * @package Zmsentities
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsentities\Helper;

class Messaging
{
    protected static $templates = array(
        'notification' => array(
            'appointment' => 'notification_appointment.twig',
            'confirmed' => 'notification_confirmation.twig',
            'queued' => 'notification_headsup.twig',
            'deleted' => 'notification_deleted.twig'
        ),
        'mail' => array(
            'appointment' => 'mail_confirmation.twig',
            'deleted' => 'mail_delete.twig'
        ),
        'ics' => array(
            'appointment' => 'icsappointment.twig',
            'deleted' => 'icsappointment_delete.twig'
        )
    );

    protected static function twigView()
    {
        $templatePath = TemplateFinder::getTemplatePath();
        $templateDldbPath = \BO\Dldb\Helper\TemplateFinder::getTemplatePath();
        $loader = new \Twig_Loader_Filesystem($templatePath);
        $loader->addPath($templateDldbPath, 'dldb');
        $twig = new \Twig_Environment($loader, array(
            //'cache' => '/cache/',
        ));
        $twig->addExtension(new TwigExtension());
        $twig->addExtension(new \Twig_Extensions_Extension_I18n());
        return $twig;
    }

    public static function getMailContent(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplateByProcessStatus('mail', $process);
        $message = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config
            )
        );
        return $message;
    }

    public static function getNotificationContent(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplateByProcessStatus('notification', $process);
        $message = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config
            )
        );
        return $message;
    }

    protected static function getTemplateByProcessStatus($type, \BO\Zmsentities\Process $process)
    {
        $status = $process->status;
        if ('confirmed' == $status &&  $process->toProperty()->queue->withAppointment->get()) {
            $status = 'appointment';
        }
        $template = null;
        if (array_key_exists($type, self::$templates)) {
            if (array_key_exists($status, self::$templates[$type])) {
                $template = self::$templates[$type][$status];
            }
        }
        return $template;
    }

    public static function getMailSubject(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = 'subjects.twig';
        $subject = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config
            )
        );
        $subject = trim($subject);
        return $subject;
    }

    public static function getMailIcs(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config, $now = false)
    {
        $ics = new \BO\Zmsentities\Ics();
        $template = self::getTemplateByProcessStatus('ics', $process);
        $message = self::getMailContent($process, $config);
        $plainContent = self::getPlainText($message, "\\n");
        $appointment = $process->getFirstAppointment();
        $icsString = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime(),
                'startTime' => $appointment->getStartTime(),
                'endTime' => $appointment->getEndTime(),
                'process' => $process,
                'timestamp' => (!$now) ? time() : $now,
                'message' => $plainContent
            )
        );
        $result = \html_entity_decode($icsString);
        $ics->content = $result;
        return $ics;
    }

    public static function getPlainText($content, $lineBreak = "\n")
    {
        $replaceThis = array(
            "<br />" => "$lineBreak",
            "<li>" => "$lineBreak- ",
            "</li>" => "",
            "<h2>" => "$lineBreak",
            "</h2>" => "$lineBreak",
        );

        $content = \preg_replace('!\s+!m', ' ', $content);
        $content = \str_replace(array_keys($replaceThis), $replaceThis, $content);
        $content = \strip_tags($content);

        /*
        $lines = \explode("\n", $content);
        $new_lines = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $new_lines[]=$line;
            }
        }
        $content = \implode("\n", $new_lines);
         */
        $content = \html_entity_decode($content);
        return $content;
    }
}
