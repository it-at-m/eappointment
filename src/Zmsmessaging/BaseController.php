<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

class BaseController
{
    protected $userLogin = null;

    public function __construct()
    {
        $this->userLogin = $this->writeLogin();
    }

    protected function writeLogin()
    {
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => '_system_messenger',
            'password' => 'zmsmessaging'
        ));
        try {
            $workstation = \App::$http
                ->readPostResult('/workstation/'. $userAccount->id .'/', $userAccount)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            //ignore double login exception on quick login
            $workstation = new \BO\Zmsentities\Workstation($exception->data);
        }

        \BO\Zmsclient\Auth::setKey($workstation->authkey);
        $workstation = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        return $workstation;
    }

    protected function writeLogout()
    {
        \App::$http->readDeleteResult('/workstation/_system_messenger/');
    }

    protected function sendMailer($mailer = null, $action = false)
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

    protected function deleteEntityFromQueue($entity)
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
