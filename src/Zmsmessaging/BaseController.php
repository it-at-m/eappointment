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
        $this->workstation = $this->writeLogin();
    }

    protected function writeLogin()
    {
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => '_system_messenger',
            'password' => 'zmsmessaging'
        ));
        try {
            $workstation = \App::$http
                ->readPostResult('/workstation/login/', $userAccount)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            //ignore double login exception on quick login
            $workstation = new \BO\Zmsentities\Workstation($exception->data);
        }

        if (array_key_exists('authkey', $workstation)) {
            \BO\Zmsclient\Auth::setKey($workstation->authkey);
        }
        $workstation = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        return $workstation;
    }

    protected function writeLogout()
    {
        \App::$http->readDeleteResult('/workstation/_system_messenger/');
    }

    protected function sendMailer(\BO\Zmsentities\Schema\Entity $entity, $mailer = null, $action = false)
    {
        if (false !== $action && null !== $mailer) {
            // @codeCoverageIgnoreStart
            if (! $mailer->Send()) {
                throw new \Exception('Zmsmessaging Failed');
                \App::$log->debug('Zmsmessaging Failed', [$mailer->ErrorInfo]);
            }
            // @codeCoverageIgnoreEnd
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = ($entity instanceof Mail) ? $entity->subject : $entity->message;
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log);
        }
        return $mailer;
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
