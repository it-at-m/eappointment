<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Status;

class StatusGet extends BaseController
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
        $validator = $request->getAttribute('validator');
        $includeProcessStats = $validator->getParameter('includeProcessStats')->isNumber()->setDefault(1)->getValue();
        $status = (new Status())->readEntity(\App::$now, $includeProcessStats);
        $status['version'] = Helper\Version::getArray();
        if (\App::DEBUG) {
            $status['opcache'] = [
                'config' => opcache_get_configuration(),
                'status' => opcache_get_status(false)
            ];
        }

        $message = Response\Message::create($request);
        $message->data = $status;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
