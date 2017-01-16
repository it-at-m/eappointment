<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
 * Handle requests concerning services
 *
 */
class WorkstationStatus extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $response = $response
                  ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                  ->withHeader('Content-Type', 'application/json')
                  ->write(json_encode([
                      'workstation' => $workstation
                  ]));

        return $response;
    }
}
