<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Slim\Render;

/**
 * Handle requests concerning services
 *
 */
class WorkstationStatus extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $workstation = $this->withFixedLastLogin($workstation);
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, ['workstation' => $workstation]);
    }

    protected function withFixedLastLogin($workstation)
    {
        $dateTime = (new \DateTime())->setTimestamp($workstation->getUseraccount()->lastLogin);
        $hour = \App::$now->format('H');
        $minute = $dateTime->format('i');
        $second = $dateTime->format('s');
        $dateTime->setTime($hour, $minute, $second);
        $workstation->getUseraccount()->lastLogin = $dateTime->getTimestamp();
        return $workstation;
    }
}
