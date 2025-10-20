<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Log as Query;

class ProcessLog extends BaseController
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
        (new Helper\User($request))->checkRights('audit');
        $searchQuery = urldecode($args['search']);
        $page = Validator::param('page')->isNumber()->setDefault(1)->getValue();
        $perPage = Validator::param('perPage')->isNumber()->setDefault(100)->getValue();
        if ($perPage > 1000) {
            $perPage = 1000;
        }

        $logList = (new Query())->readByProcessData($searchQuery, $page, $perPage);

        $message = Response\Message::create($request);
        $message->data = $logList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
