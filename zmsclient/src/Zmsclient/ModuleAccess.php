<?php

namespace BO\Zmsclient;

use BO\Zmsentities\Workstation;
use Psr\Http\Message\ResponseInterface;

class ModuleAccess
{
    public const MODULE_ADMIN = 'zmsadmin';

    public const MODULE_STATISTIC = 'zmsstatistic';

    public static function rejectWrongModuleAccess(
        string $application,
        Workstation $workstation,
        ResponseInterface $response
    ): ?ResponseInterface {
        $useraccount = $workstation->getUseraccount();

        if ($useraccount->isSuperUser()) {
            return null;
        }

        $rejected = ($application === self::MODULE_STATISTIC && !$useraccount->hasPermissions(['statistic']))
            || ($application === self::MODULE_ADMIN && $useraccount->hasExclusivePermission('statistic'));

        if ($rejected) {
            self::endSession($workstation);
            $template = $application === self::MODULE_ADMIN
                ? 'exception/bo/slim/exception/wrongmodulestatistic.twig'
                : 'exception/bo/slim/exception/wrongmoduleadmin.twig';
            return \BO\Slim\Render::withHtml($response, $template, [], 403);
        } else {
            return null;
        }
    }

    private static function endSession(Workstation $workstation): void
    {
        try {
            if (Auth::getKey()) {
                \App::$http->readDeleteResult('/workstation/login/' . $workstation->getUseraccount()->id . '/');
            }
        } catch (\Exception $exception) {
        }

        Auth::removeKey();
    }
}
