<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use Psr\Http\Message\ResponseInterface;

class ModuleAccess
{
    public const MODULE_ADMIN = 'zmsadmin';

    public const MODULE_STATISTIC = 'zmsstatistic';

    public static function rejectWrongModuleAccess(
        string $application,
        Useraccount $useraccount,
        ResponseInterface $response,
        ?Workstation $workstation = null
    ): ?ResponseInterface {
        if ($useraccount->isSuperUser()) {
            return null;
        }

        $rejected = ($application === self::MODULE_STATISTIC && !$useraccount->hasPermissions(['statistic']))
            || ($application === self::MODULE_ADMIN && $useraccount->hasExclusivePermission('statistic'));

        if (!$rejected) {
            return null;
        }

        self::endSession($workstation);

        $template = $application === self::MODULE_ADMIN
            ? 'exception/bo/slim/exception/wrongmodulestatistic.twig'
            : 'exception/bo/slim/exception/wrongmoduleadmin.twig';

        return \BO\Slim\Render::withHtml($response, $template, [], 403);
    }

    private static function endSession(?Workstation $workstation = null): void
    {
        if (!Auth::getKey() && $workstation instanceof Workstation && !empty($workstation->authkey)) {
            Auth::setKey($workstation->authkey, time() + \App::SESSION_DURATION);
        }
        if (!Auth::getKey()) {
            return;
        }
        try {
            if (!$workstation instanceof Workstation || !$workstation->hasId()) {
                $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])?->getEntity();
            }
            if ($workstation instanceof Workstation && $workstation->hasId()) {
                \App::$http->readDeleteResult('/workstation/login/' . $workstation->getUseraccount()->id . '/');
            }
        } catch (\Exception $exception) {
        }
        Auth::removeKey();
    }
}
