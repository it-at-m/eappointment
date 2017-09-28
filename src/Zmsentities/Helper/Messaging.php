<?php
/**
 *
 * @package Zmsentities
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsentities\Helper;

use \BO\Zmsentities\Process;

use \BO\Zmsentities\Config;

class Messaging
{
    public static $icsRequiredForStatus = [
        'confirmed',
        'deleted'
    ];

    protected static $templates = array(
        'notification' => array(
            'appointment' => 'notification_appointment.twig',
            'confirmed' => 'notification_confirmation.twig',
            'queued' => 'notification_headsup.twig',
            'pickup' => 'notification_pickup.twig',
            'deleted' => 'notification_deleted.twig'
        ),
        'mail' => array(
            'queued' => 'mail_queued.twig',
            'appointment' => 'mail_confirmation.twig',
            'pickup' => 'mail_pickup.twig',
            'deleted' => 'mail_delete.twig',
            'blocked' => 'mail_delete.twig',
            'survey' => 'mail_survey.twig'
        ),
        'ics' => array(
            'appointment' => 'icsappointment.twig',
            'deleted' => 'icsappointment_delete.twig'
        ),
        'admin' => array(
            'deleted' => 'mail_admin_delete.twig',
            'blocked' => 'mail_admin_delete.twig',
            'updated' => 'mail_admin_update.twig'
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

    public static function getMailContent(Process $process, Config $config, $initiator = null)
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplateByProcessStatus('mail', $process);
        if ($initiator) {
            $template = self::getTemplateByProcessStatus('admin', $process);
        }
        $message = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config,
                'initiator' => $initiator
            )
        );
        return $message;
    }

    public static function getNotificationContent(Process $process, Config $config)
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

    protected static function getTemplateByProcessStatus($type, Process $process)
    {
        $status = $process->status;
        if ('confirmed' == $status &&  $process->toProperty()->queue->withAppointment->get()) {
            $status = 'appointment';
        }
        if ('finished' == $status &&  $process->getFirstClient()->hasSurveyAccepted()) {
            $status = 'survey';
        }
        $template = null;
        if (array_key_exists($type, self::$templates)) {
            if (array_key_exists($status, self::$templates[$type])) {
                $template = self::$templates[$type][$status];
            }
        }
        return $template;
    }

    public static function getMailSubject(Process $process, Config $config, $initiator = null)
    {
        $appointment = $process->getFirstAppointment();
        $template = 'subjects.twig';
        $subject = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config,
                'initiator' => $initiator
            )
        );
        $subject = trim($subject);
        return $subject;
    }

    public static function getMailIcs(Process $process, Config $config, $now = false)
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
                'message' => trim($plainContent)
            )
        );
        $result = \html_entity_decode($icsString);
        $ics->content = $result;
        return $ics;
    }

    public static function getPlainText($content, $lineBreak = "\n")
    {
        $converter = new \League\HTMLToMarkdown\HtmlConverter();
        $text = $converter->convert($content);
        $text = strip_tags($text);
        $text = str_replace("\n", $lineBreak, $text);
        //error_log(">>>$text<<<");
        return $text;
    }
}
