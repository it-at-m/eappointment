<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Useraccount;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeAvailabilityHistory extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $useraccount = $workstation->getUseraccount();
        if (!static::canViewAvailabilityHistory($useraccount)) {
            throw new UserAccountMissingRights();
        }

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $from = Validator::param('from')->isString()->getValue();
        $to = Validator::param('to')->isString()->getValue();
        $availabilityId = Validator::param('availabilityId')->isNumber()->getValue();
        $action = Validator::param('action')->isString()->getValue();
        $params = array_filter([
            'from' => $from,
            'to' => $to,
            'availabilityId' => $availabilityId,
            'action' => $action,
        ], static function ($value) {
            return $value !== null && $value !== '';
        });

        $result = \App::$http->readGetResult(
            '/scope/' . $scopeId . '/availability/history/',
            $params
        );

        return Render::withJson($response, [
            'data' => $result->getData() ?? [],
        ]);
    }

    public static function canViewAvailabilityHistory(Useraccount $useraccount): bool
    {
        return $useraccount->isSuperUser() || $useraccount->hasRole('system_admin');
    }
}
