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
use Psr\Http\Message\RequestInterface;

class Access extends \BO\Slim\Controller
{
    protected mixed $workstation = null;

    protected mixed $organisation = null;

    protected mixed $department = null;

    protected int $resolveLevel = 2;

    protected bool $withAccess = true;

    protected mixed $owner = null;

    protected function initAccessRights(RequestInterface $request): void
    {
        $this->workstation = $this->readWorkstation();
        if ($this->workstation && isset($this->workstation->scope['id']) && $this->workstation->scope['id'] > 0) {
            $this->department = $this->readDepartment();
            $this->organisation = $this->readOrganisation();
            $this->owner = $this->readOwner();
        }
        $this->validateAccessRights($request);
    }

    protected function readWorkstation(): mixed
    {
        $workstation = \App::http()->readGetResult('/workstation/', ['resolveReferences' => $this->resolveLevel]);
        return $workstation->getEntity();
    }

    protected function readDepartment(): mixed
    {
        if ($this->workstation->getUseraccount()->hasPermissions(['statistic'])) {
            return \App::http()
                ->readGetResult('/scope/' . $this->workstation->scope['id'] . '/department/')
                ->getEntity();
        }
    }

    protected function readOrganisation(): mixed
    {
        if ($this->workstation->getUseraccount()->isSuperUser()) {
            return \App::http()
                ->readGetResult('/department/' . $this->department->getId() . '/organisation/')
                ->getEntity();
        }
    }

    protected function readOwner(): mixed
    {
        if ($this->workstation->getUseraccount()->isSuperUser()) {
            return \App::http()
                ->readGetResult('/organisation/' . $this->organisation->getId() . '/owner/')
                ->getEntity();
        }
    }

    protected function validateAccessRights(RequestInterface $request): void
    {
        $path = $request->getUri()->getPath();
        $this->validateAccess($path);
        $this->validateScope($path);
    }

    protected function validateAccess(string $path): void
    {
        if (
            (false !== strpos($path, 'owner') && ! $this->owner) ||
            (false !== strpos($path, 'organisation') && ! $this->organisation) ||
            (false !== strpos($path, 'department') && ! $this->department)
        ) {
            throw new UserAccountAccessRightsFailed();
        }
    }

    protected function validateScope(string $path): void
    {
        if (
            $this->isPathWithoutScope($path)
            && (! isset($this->workstation['scope']) || ! isset($this->workstation['scope']['id']))
        ) {
            throw new WorkstationMissingScope();
        }
    }

    protected function isPathWithoutScope(string $path): bool
    {
        // TODO: refactor to integrate these access rules in the controller to make them visible
        return (false === strpos($path, 'select')
            && false === strpos($path, 'warehouse')
            && false === strpos($path, 'logout')
            && false === strpos($path, 'report')
        );
    }

    /**
     * @return (mixed|string|string[][][])[]|Workstation
     *
     */
    protected function testLogin(mixed $input)
    {
        $userAccount = new Useraccount(array(
            'id' => $input['loginName'],
            'password' => $input['password'],
            'departments' => array('id' => 0) // required in schema validation
        ));
        try {
            /** @var Workstation $workstation */
            $workstation = \App::http()->readPostResult('/workstation/login/', $userAccount)->getEntity();
            return $workstation;
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = TwigExceptionHandler::getExceptionTemplate($exception);
            if ('BO\Zmsentities\Exception\SchemaValidation' == $exception->template) {
                $exceptionData = [
                  'template' => 'exception/bo/zmsbackend/useraccount/exception/invalidcredentials.twig'
                ];
                $exceptionData['data']['password']['messages'] = [
                    'Der Nutzername oder das Passwort wurden falsch eingegeben'
                ];
            } elseif ('BO\Zmsbackend\Useraccount\Exception\UserAlreadyLoggedIn' == $exception->template) {
                Auth::setKey($exception->data['authkey'], time() + \App::SESSION_DURATION);
                throw $exception;
            } elseif (
                '' != $exception->template
                && $this->exceptionTemplateExists($template)
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

    protected function exceptionTemplateExists(string $template): bool
    {
        /** @var mixed $container */
        $container = \App::$slim->getContainer();
        return $container->get('view')->getLoader()->exists($template);
    }
}
