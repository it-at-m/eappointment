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

    public static function createMessage(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = $process->status . 'Message.twig';
        $message = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config
            )
        );
            return \base64_encode($message);
    }

    public static function createNotificationMessage(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
    {
        $appointment = $process->getFirstAppointment();
        $template = 'notifications.twig';
        $message = self::twigView()->render(
            'messaging/' . $template,
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => $config
            )
        );
            return \base64_encode($message);
    }

    public static function createSubject(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config)
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

    public static function createIcs(\BO\Zmsentities\Process $process, \BO\Zmsentities\Config $config, $now = false)
    {
        $ics = new \BO\Zmsentities\Ics();
        $template = 'icsappointment.twig';
        $message = \base64_decode(self::createMessage($process, $config));
        $plainContent = (new \BO\Zmsentities\Mail())->toPlainText($message);
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
        $ics->content = \base64_encode($result);
        return $ics;
    }
}
