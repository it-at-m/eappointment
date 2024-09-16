<?php
/**
 *
 * @package Zmsentities
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsentities\Helper;

use BO\Zmsentities\Client;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Collection\ProcessList;
use \BO\Zmsentities\Config;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Extra\Intl\IntlExtension;

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

    public static $allowEmptyProcesses = [
        'overview'
    ];

    public static function isIcsRequired(
        \BO\Zmsentities\Config $config,
        \BO\Zmsentities\Process $process,
        $status
    ) {
        $client = $process->getFirstClient();
        $noAttachmentDomains = $config->toProperty()->notifications->noAttachmentDomains->get();
        $noAttachmentDomains = explode(',', (string)$noAttachmentDomains);
        foreach ($noAttachmentDomains as $matching) {
            if (trim($matching) && strpos($client->email, '@'.trim($matching))) {
                return false;
            }
        }
        return (in_array($status, self::$icsRequiredForStatus));
    }

    public static function isEmptyProcessListAllowed($status)
    {
        return (in_array($status, self::$allowEmptyProcesses));
    }

    protected static $templates = array(
        'notification' => array(
            'appointment' => 'notification_appointment.twig',
            'confirmed' => 'notification_confirmation.twig',
            'queued' => 'notification_confirmation.twig',
            'called' => 'notification_headsup.twig',
            'reminder' => 'notification_reminder.twig',
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
            'survey' => 'mail_survey.twig',
            'overview' => 'mail_processlist_overview.twig',
            'preconfirmed' => 'mail_preconfirmed.twig'
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

    protected static function twigView(): Environment
    {
        $templatePath = TemplateFinder::getTemplatePath();
        $customTemplatesPath = 'custom_templates/';

        if (getenv("ZMS_CUSTOM_TEMPLATES_PATH")) {
            $customTemplatesPath = getenv("ZMS_CUSTOM_TEMPLATES_PATH");
        }

        $initialTemplatePaths = [];

        if (is_dir($customTemplatesPath)) {
            $initialTemplatePaths[] = $customTemplatesPath;
        }

        $initialTemplatePaths[] = $templatePath;

        $loader = new FilesystemLoader($initialTemplatePaths);
        
        if (is_dir($customTemplatesPath)) {
            $loader->addPath($customTemplatesPath, 'zmsentities');
        }

        $loader->addPath($templatePath, 'zmsentities');
        $twig = new Environment($loader, array(
            //'cache' => '/cache/',
        ));
        $twig->addExtension(new TranslationExtension());
        $twig->addExtension(new IntlExtension());
        return $twig;
    }

    protected static function dbTwigView($templateProvider) 
    {
        $loader = new \Twig\Loader\ArrayLoader($templateProvider->getTemplates());
        $twig = new \Twig\Environment($loader);
        $twig->addExtension(new TranslationExtension());
        $twig->addExtension(new IntlExtension());
        return $twig;

    }


    public static function getMailContent(
        $processList,
        Config $config,
        $initiator = null,
        $status = 'appointment',
        $templateProvider = false
    ) {
        $collection = (new ProcessList)->testProcessListLength($processList, self::isEmptyProcessListAllowed($status));
        $mainProcess = $collection->getFirst();
        $date = (new \DateTimeImmutable())->setTimestamp(0);
        $client = (new Client());
        if ($mainProcess) {
            $collection = $collection->withoutProcessByStatus($mainProcess, $status);
            $date = $mainProcess->getFirstAppointment()->toDateTime()->format('U');
            $client = $mainProcess->getFirstClient();
        }

        $template = self::getTemplate('mail', $status, $mainProcess);
        if ($initiator) {
            $template = self::getTemplate('admin', $status);
        }
        if (!$template) {
            $exception = new \BO\Zmsentities\Exception\TemplateNotFound("Template for status $status not found");
            $exception->data = $status;
            throw $exception;
        }

        $requestGroups = [];
        if ($mainProcess) {
            foreach ($mainProcess->requests as $request) {
                if (! isset($requestGroups[$request->id])) {
                    $requestGroups[$request->id] = [
                        'request' => $request,
                        'count' => 0
                    ];
                }
                $requestGroups[$request->id]['count']++;
            }
        }

        $parameters = [
            'date' => $date,
            'client' => $client,
            'process' => $mainProcess,
            'requestGroups' => $requestGroups,
            'processList' => $collection->sortByAppointmentDate(),
            'config' => $config,
            'initiator' => $initiator,
            'appointmentLink' => base64_encode(json_encode([
                'id' => $mainProcess ? $mainProcess->id : '',
                'authKey' => $mainProcess ? $mainProcess->authKey : ''
            ]))
        ];

        if ($templateProvider) {
            $message = self::dbTwigView($templateProvider)->render($template, $parameters);
        } else {
            $message = self::twigView()->render('messaging/' . $template, $parameters);
        }

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

    public static function getNotificationContent(Process $process, Config $config, $status = 'appointment')
    {
        $appointment = $process->getFirstAppointment();
        $template = self::getTemplate('notification', $status);
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

    protected static function getTemplate($type, $status, ?Process $process = null)
    {
        $template = null;
        if (Property::__keyExists($type, self::$templates)) {
            if (Property::__keyExists($status, self::$templates[$type])) {
                $template = self::$templates[$type][$status];
            }
        }

        /*

        // temporarily disable until we have all the customized templates in the database

        if ($process) {
            $provider = $process->scope->provider;
            //error_log($provider);
            //print_r($provider, true);
            $providerName = $provider->displayName;
            $providerTemplateName = self::getProviderTemplateName($providerName);
            $providerTemplateFolder = 'custom/' . $providerTemplateName . '/';

            if (file_exists(TemplateFinder::getTemplatePath() . '/messaging/' . $providerTemplateFolder . $template)) {
                return $providerTemplateFolder . $template;
            }
        }

        */

        return $template;
    }

    private static function getProviderTemplateName($providerName)
    {
        if (strpos($providerName, '(')) {
            $providerName = substr($providerName, 0, strpos($providerName, '('));
        }
        $divider = '-';
        $providerTemplate = preg_replace('~[^\pL\d]+~u', $divider, $providerName);
        $providerTemplate = iconv('utf-8', 'us-ascii//TRANSLIT', $providerTemplate);
        $providerTemplate = preg_replace('~[^-\w]+~', '', $providerTemplate);
        $providerTemplate = trim($providerTemplate, $divider);
        $providerTemplate = preg_replace('~-+~', $divider, $providerTemplate);
        $providerTemplate = strtolower($providerTemplate);

        if (empty($providerTemplate)) {
            return 'none';
        }

        return $providerTemplate;
    }

    public static function getMailSubject(
        Process $process,
        Config $config,
        $initiator = null,
        $status = 'appointment'
    ) {
        $appointment = $process->getFirstAppointment();
        $template = 'subjects.twig';
        $subject = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config,
                'initiator' => $initiator,
                'status' => $status
            )
        );
        $subject = trim($subject);
        return $subject;
    }

    public static function getMailIcs(
        Process $process,
        Config $config,
        $status = 'appointment',
        $initiator = null,
        $now = false,
        $templateProvider = false
    ) {
        $ics = new \BO\Zmsentities\Ics();
        $template = self::getTemplate('ics', $status);
        $message = self::getMailContent($process, $config, $initiator, $status, $templateProvider);

        error_log("+++++++");
        error_log(json_encode($message));
        error_log("+++++++");


        error_log("******");
        error_log(json_encode($message));
        error_log("******");
        $plainContent = self::getPlainText($message, "\\n");
        $appointment = $process->getFirstAppointment();
        $currentYear = $appointment->getStartTime()->format('Y');
        error_log("---------------");
        error_log(json_encode($template));
        error_log("---------------");
        $icsString = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'startTime' => $appointment->getStartTime()->format('U'),
                'endTime' => $appointment->getEndTime()->format('U'),
                'startSummerTime' =>
                    \BO\Zmsentities\Helper\DateTime::getSummerTimeStartDateTime($currentYear)->format('U'),
                'endSummerTime' =>
                    \BO\Zmsentities\Helper\DateTime::getSummerTimeEndDateTime($currentYear)->format('U'),
                'process' => $process,
                'timestamp' => (!$now) ? time() : $now,
                'message' => $plainContent
            )
        );

        $ics->content = self::generateIcsContent($process, $config, $status, $now, $templateProvider, $message);

        return $ics;
    }

    protected static function generateIcsContent(
        Process $process,
        Config $config,
        $status = 'appointment',
        $now = false,
        $templateProvider = false,
        $message = '' // Pass $message from getMailIcs, or query if not set
    ) {
        // If $message is not provided, retrieve it from the getMailContent query
        if (empty($message)) {
            $message = self::getMailContent($process, $config, null, $status, $templateProvider);
        }
    
        // Convert the email message to plain text for the ICS description
        $plainTextDescription = self::getPlainText($message);
    
        // Log the description to make sure it's being generated
        error_log("################## Plain Text Description ##################");
        error_log(json_encode($plainTextDescription));
        error_log("############################################################");
    
        // Get the ICS template for the process status dynamically
        $template = self::getTemplate('ics', $status, $process);
        if (!$template) {
            throw new \Exception("ICS template for status $status not found");
        }
    
        // Extract the first appointment details
        $appointment = $process->getFirstAppointment();
        $currentYear = $appointment->getStartTime()->format('Y');
    
        // Prepare parameters for ICS rendering, including the plain text description
        $parameters = [
            'date' => $appointment->toDateTime()->format('U'),
            'startTime' => $appointment->getStartTime()->format('U'),
            'endTime' => $appointment->getEndTime()->format('U'),
            'startSummerTime' => \BO\Zmsentities\Helper\DateTime::getSummerTimeStartDateTime($currentYear)->format('U'),
            'endSummerTime' => \BO\Zmsentities\Helper\DateTime::getSummerTimeEndDateTime($currentYear)->format('U'),
            'process' => $process,
            'timestamp' => (!$now) ? time() : $now,
            'message' => $plainTextDescription // Pass the plain text email content to the ICS template
        ];
    
        // Log the parameters to ensure the description is in the parameters being passed to the template
        error_log("################## PARAMETERS ##################");
        error_log(json_encode($parameters));
        error_log("################################################");
    
        // Render the ICS content using Twig and the fetched template
        if ($templateProvider) {
            $icsString = self::dbTwigView($templateProvider)->render($template, $parameters);
        } else {
            $icsString = self::twigView()->render('messaging/' . $template, $parameters);
        }
    
        // Log the final output
        error_log("################## FINAL ICS STRING ##################");
        error_log($icsString);
        error_log("######################################################");
    
        // Decode HTML entities to plain text and ensure lines follow ICS standards
        $icsString = html_entity_decode($icsString);
        return self::getTextWithFoldedLines($icsString);
    }
    
    
    
    
    
    
        

    public static function getPlainText($content, $lineBreak = "\n")
    {
        $converter = new \League\HTMLToMarkdown\HtmlConverter();
        $converter->getConfig()->setOption('remove_nodes', 'script');
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getConfig()->setOption('hard_break', true);
        $converter->getConfig()->setOption('use_autolinks', false);
        $text = $converter->convert($content);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace("\n", $lineBreak, $text);
        return trim($text);
    }

    public static function getTextWithFoldedLines($content)
    {
        $newLines = [];
        $lines = explode("\n", $content);
        foreach ($lines as $text) {
            $subline = '';
            while (strlen($text) > 75) {
                $line = mb_substr($text, 0, 72);
                $llength = mb_strlen($line);
                $subline .= $line.chr(13).chr(10).chr(32);
                $text = mb_substr($text, $llength);
            }
            if (!empty($text) && 0 < strlen($subline)) {
                $subline .= $text;
            }
            if (0 < strlen($subline)) {
                $newLines[] = $subline;
            }
            if (!empty($text) && '' == $subline) {
                $newLines[] = $text;
            }
        }
        return implode(chr(13).chr(10), $newLines);
    }
}
