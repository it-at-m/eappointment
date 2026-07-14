<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Dayoff\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Dayoff\Service\DayOff as Query;

class DayoffList extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('dayoff');
        if ($args['year'] > \App::$now->modify('+ 10years')->format('Y') || $args['year'] < \App::$now->format('Y')) {
            throw new \BO\Zmsbackend\Dayoff\Exception\YearOutOfRange();
        }
        $dayOffList = (new Query())->readCommonByYear($args['year']);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $dayOffList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
