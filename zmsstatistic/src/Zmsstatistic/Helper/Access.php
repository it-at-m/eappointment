<?php

/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsstatistic\Helper;

use BO\Zmsclient\Auth;
use BO\Zmsentities\Exception\UserAccountAccessRightsFailed;
use BO\Zmsentities\Exception\WorkstationMissingScope;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Application;

class Access extends \BO\Slim\Controller
{
    protected $workstation = null;

    protected $organisation = null;

    protected $department = null;

    protected $resolveLevel = 2;

    protected $withAccess = true;

    protected $owner = null;

    protected function initAccessRights($request)
    {
        $this->workstation = $this->readWorkstation();
        if ($this->workstation && isset($this->workstation->scope['id'])) {
            $this->department = $this->readDepartment();
            $this->organisation = $this->readOrganisation();
            $this->owner = $this->readOwner();
        }
        $this->testAccessRights($request);
    }

    protected function readWorkstation()
    {
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

    protected function readOwner()
    {
        if ($this->workstation->getUseraccount()->isSuperUser()) {
            return \App::$http
                ->readGetResult('/organisation/' . $this->organisation->getId() . '/owner/')
                ->getEntity();
        }
    }

    protected function testAccessRights($request)
    {
        $path = $request->getUri()->getPath();
        $this->testAccess($path);
        //$this->testScope($path);
    }

    protected function testAccess($path)
    {
        if (
            (false !== strpos($path, 'owner') && ! $this->owner) ||
            (false !== strpos($path, 'organisation') && ! $this->organisation) ||
            (false !== strpos($path, 'department') && ! $this->department)
        ) {
            throw new UserAccountAccessRightsFailed();
        }
    }

    protected function testScope($path)
    {
        if (
            $this->isPathWithoutScope($path)
            && (! isset($this->workstation['scope']) || ! isset($this->workstation['scope']['id']))
        ) {
            throw new WorkstationMissingScope();
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

    protected function testLogin($input)
    {
        $userAccount = new Useraccount(array(
            'id' => $input['loginName'],
            'password' => $input['password'],
            'departments' => array('id' => 0) // required in schema validation
        ));
        try {
            /** @var Workstation $workstation */
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            return $workstation;
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = TwigExceptionHandler::getExceptionTemplate($exception);
            if ('BO\Zmsentities\Exception\SchemaValidation' == $exception->template) {
                $exceptionData = [
                  'template' => 'exception/bo/zmsapi/exception/useraccount/invalidcredentials.twig'
                ];
                $exceptionData['data']['password']['messages'] = [
                    'Der Nutzername oder das Passwort wurden falsch eingegeben'
                ];
            } elseif ('BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn' == $exception->template) {
                Auth::setKey($exception->data['authkey'], time() + \App::SESSION_DURATION);
                throw $exception;
            } elseif (
                '' != $exception->template
                && \App::$slim->getContainer()->get('view')->getLoader()->exists($template)
            ) {
                $exceptionData = [
                  'template' => $template,
                  'data' => $exception->data
                ];
            } else {
                throw $exception;
            }
        }
        return $exceptionData;
    }
}
