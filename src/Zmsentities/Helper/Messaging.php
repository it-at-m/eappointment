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

use \BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(Coupling)
 *
 */
class Messaging
{
    public static $icsRequiredForStatus = [
        'confirmed',
        'appointment',
        'deleted'
    ];

    public static function isIcsRequired(
        \BO\Zmsentities\Config $config,
        \BO\Zmsentities\Process $process
    ) {
        $status = static::getMessagingStatus($process);
        $client = $process->getFirstClient();
        $noAttachmentDomains = $config->toProperty()->notifications->noAttachmentDomains->get();
        $noAttachmentDomains = explode(',', (string)$noAttachmentDomains);
        foreach ($noAttachmentDomains as $matching) {
            if (trim($matching) && strpos($client->email, '@'.trim($matching))) {
                return false;
            }
        }
        return (in_array($status, Messaging::$icsRequiredForStatus));
    }

    protected static $templates = array(
        'notification' => array(
            'appointment' => 'notification_appointment.twig',
            'confirmed' => 'notification_confirmation.twig',
            'queued' => 'notification_confirmation.twig',
            'called' => 'notification_headsup.twig',
            'reminder' => 'notification_headsup.twig',
            'pickup' => 'notification_pickup.twig',
            'deleted' => 'notification_deleted.twig'
        ),
        'mail' => array(
            'queued' => 'mail_queued.twig',
            'appointment' => 'mail_confirmation.twig',
            'reminder' => 'mail_reminder.twig',
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
        $twig->addExtension(new \Twig_Extensions_Extension_I18n());
        $twig->addExtension(new \Twig_Extensions_Extension_Intl());
        return $twig;
    }

    public static function getMailContent(Process $process, Config $config, $initiator = null)
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplateByProcessStatus('mail', $process);
        if ($initiator) {
            $template = self::getTemplateByProcessStatus('admin', $process);
        }
        if (!$template) {
            $exception = new \BO\Zmsentities\Exception\TemplateNotFound("Template for $process not found");
            $exception->data = $process;
            throw $exception;
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

    public static function getScopeAdminProcessListContent(
        \BO\Zmsentities\Collection\ProcessList $processList,
        \BO\Zmsentities\Scope $scope,
        \DateTimeInterface $dateTime
    ) {
        $message = self::twigView()->render(
            'messaging/mail_scopeadmin_processlist.twig',
            array(
                'dateTime' => $dateTime,
                'processList' => $processList,
                'scope' => $scope
            )
        );
        return $message;
    }

    public static function getNotificationContent(Process $process, Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplateByProcessStatus('notification', $process);
        if (!$template) {
            $exception = new \BO\Zmsentities\Exception\TemplateNotFound("Template for $process not found");
            $exception->data = $process;
            throw $exception;
        }
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
        $status = self::getMessagingStatus($process);
        $template = null;
        if (Property::__keyExists($type, self::$templates)) {
            if (Property::__keyExists($status, self::$templates[$type])) {
                $template = self::$templates[$type][$status];
            }
        }
        return $template;
    }

    public static function getMessagingStatus(Process $process)
    {
        $status = $process->status;
        if (('confirmed' == $status || 'queued' == $status) && $process->isWithAppointment()) {
            $status = 'appointment';
        }
        return $status;
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
                'date' => $appointment->toDateTime()->format('U'),
                'startTime' => $appointment->getStartTime()->format('U'),
                'endTime' => $appointment->getEndTime()->format('U'),
                'process' => $process,
                'timestamp' => (!$now) ? time() : $now,
                'message' => $plainContent
            )
        );
        $result = \html_entity_decode($icsString);
        $ics->content = self::getTextWithFoldedLines($result);
        return $ics;
    }

    public static function getPlainText($content, $lineBreak = "\n")
    {
        $converter = new \League\HTMLToMarkdown\HtmlConverter();
        $converter->getConfig()->setOption('remove_nodes', 'script');
        $converter->getConfig()->setOption('strip_tags', true);
        $text = $converter->convert($content);
        $text = str_replace("\n", $lineBreak, $text);
        return addslashes(trim($text));
    }

    public static function getTextWithFoldedLines($content)
    {
        $newLines = [];
        $lines = explode("\n", $content);
        foreach ($lines as $index => $text) {
            while (strlen($text) > 74) {
                $line = mb_substr($text, 0, 74);
                $llength = mb_strlen($line);
                $newLines[] = $line.chr(32);
                $text = mb_substr($text, $llength);
            }
            if (!empty($text)) {
                $newLines[] = $text;
            }
        }
        return implode("\n", $newLines);
    }
}
