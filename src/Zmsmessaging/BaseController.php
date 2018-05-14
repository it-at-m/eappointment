<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mail;
use \BO\Zmsentities\Notification;
use \BO\Zmsentities\Mimepart;

class BaseController
{
    protected $workstation = null;

    public function __construct()
    {
        \App::$http->setUserInfo('_system_messenger', 'zmsmessaging');
    }

    protected function sendMailer(\BO\Zmsentities\Schema\Entity $entity, $mailer = null, $action = false)
    {
        // @codeCoverageIgnoreStart
        if (false !== $action && null !== $mailer && ! $mailer->Send()) {
            throw new Exception\SendingFailed();
        }
        // @codeCoverageIgnoreEnd
        $log = new Mimepart(['mime' => 'text/plain']);
        $log->content = ($entity instanceof Mail) ? $entity->subject : $entity->message;
        \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log);
        return $mailer;
    }

    protected function removeEntityOlderThanOneHour($entity)
    {
        if (3600 < \App::$now->getTimestamp() - $entity->createTimestamp) {
            $this->deleteEntityFromQueue($entity);
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = 'Zmsmessaging Failure: Queue entry older than 1 hour has been removed';
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            \App::$log->warning($log->content);
            return false;
        }
    }

    public function deleteEntityFromQueue($entity)
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
