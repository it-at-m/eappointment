<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

class Access extends \BO\Slim\Controller
{
    protected $workstation = null;

    protected $organisation = null;

    protected $department = null;

    protected $resolveLevel = 2;

    protected function initAccessRights($request)
    {
        $this->workstation = $this->readWorkstation($request);
        if ($this->workstation && $this->workstation->scope['id']) {
            $this->department = $this->readDepartment();
            $this->organisation = $this->readOrganisation();
        } elseif (! $this->workstation || ! $this->workstation->hasId()) {
            return \BO\Slim\Render::redirect('index', array('login_failed' => 1));
        }
        $this->testAccessRights($request);
    }

    protected function readWorkstation($request)
    {
        $workstation = $this->workstation;
        $path = $request->getUri()->getPath();
        if ('/' != $path) {
            $workstation = \App::$http
                ->readGetResult('/workstation/', ['resolveReferences' => $this->resolveLevel])
                ->getEntity();
        } else {
            try {
                $workstation = \App::$http
                    ->readGetResult('/workstation/', ['resolveReferences' => $this->resolveLevel])
                    ->getEntity();
            } catch (\BO\Zmsclient\Exception $exception) {
                if ('BO\Zmsentities\Exception\UserAccountMissingLogin' != $exception->template) {
                    throw $exception;
                }
            }
        }
        return $workstation;
    }

    protected function readDepartment()
    {
        if ($this->workstation->getUseraccount()->hasRights(['department'])) {
            return \App::$http
                ->readGetResult('/scope/' . $this->workstation->scope['id'] . '/department/')
                ->getEntity();
        }
    }

    protected function readOrganisation()
    {
        if ($this->workstation->getUseraccount()->hasRights(['organisation'])) {
            return \App::$http
                ->readGetResult('/department/' . $this->department->getId() . '/organisation/')
                ->getEntity();
        }
    }

    protected function testAccessRights($request)
    {
        $path = $request->getUri()->getPath();
        if (false !== strpos($path, 'organisation') && ! $this->organisation) {
            throw new \BO\Zmsentities\Exception\UserAccountAccessRightsFailed();
        }
        if (false !== strpos($path, 'department') && ! $this->department) {
            throw new \BO\Zmsentities\Exception\UserAccountAccessRightsFailed();
        }
        if (! strpos($path, 'select') &&
            (! isset($this->workstation['scope']) || ! isset($this->workstation['scope']['id']))
        ) {
            throw new \BO\Zmsentities\Exception\WorkstationMissingScope();
        }
    }

    protected function testLogin($loginData, $response)
    {
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => $loginData['loginName']['value'],
            'password' => $loginData['password']['value']
        ));
        try {
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            if (array_key_exists('authkey', $workstation)) {
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn') {
                \BO\Zmsclient\Auth::setKey($exception->data['authkey']);
                throw $exception;
            } elseif ($exception->template == 'BO\Zmsapi\Exception\Useraccount\AuthKeyFound') {
                throw $exception;
            }
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung gescheitert',
                'loginfailed' => true,
                'workstation' => null,
                'loginData' => $loginData
            )
        );
    }
}
