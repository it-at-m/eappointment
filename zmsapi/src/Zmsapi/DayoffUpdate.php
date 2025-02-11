<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\DayOff as Query;

class DayoffUpdate extends BaseController
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
        (new Helper\User($request))->checkRights('superuser');

        $query = new Query();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $collection = new \BO\Zmsentities\Collection\DayoffList($input);
        $collection->testDatesInYear($args['year']);
        $collection = $query->writeCommonDayoffsByYear($input, $args['year']);

        $message = Response\Message::create($request);
        $message->data = $collection;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
