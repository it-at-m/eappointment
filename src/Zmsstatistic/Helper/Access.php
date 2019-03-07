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

    protected $withAccess = true;

    protected function initAccessRights($request)
    {
        $this->workstation = $this->readWorkstation($request);
        if ($this->workstation && isset($this->workstation->scope['id'])) {
            $this->department = $this->readDepartment();
            $this->organisation = $this->readOrganisation();
        }
        $this->testAccessRights($request);
    }

    protected function readWorkstation()
    {
        $workstation = $this->workstation;
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => $this->resolveLevel]);
        return ($workstation) ? $workstation->getEntity() : null;
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
        if ($this->isPathWithoutScope($path)
            && (! isset($this->workstation['scope']) || ! isset($this->workstation['scope']['id']))
        ) {
            throw new \BO\Zmsentities\Exception\WorkstationMissingScope();
        }
    }

    protected function isPathWithoutScope($path)
    {
        // TODO: refactor to integrate these access rules in the controller to make them visible
        return (false === strpos($path, 'select')
            && false === strpos($path, 'warehouse')
            && false === strpos($path, 'logout')
        );
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
