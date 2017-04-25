<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

class Transmission
{
    public static function sendMailer($mailer, $action)
    {
        if (false !== $action) {
            if (null !== $mailer) {
                if (! $mailer->Send()) {
                    \App::$log->debug('Zmsmessaging Failed', [$mailer->ErrorInfo]);
                }
            }
        }
        // @codeCoverageIgnoreEnd
        return $mailer;
    }

    public static function deleteEntityFromQueue($entity)
    {
        $type = ($entity instanceof \BO\Zmsentities\Mail) ? 'mails' : 'notification';
        try {
            $entity = \App::$http->readDeleteResult('/'. $type .'/'. $entity->id .'/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            throw $exception;
        }
        return ($entity) ? true : false;
    }
}
