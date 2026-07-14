<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Status\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Status\Service\Status;

class StatusGet extends \BO\Zmsbackend\Api\BaseController
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
        $validator = $request->getAttribute('validator');
        $includeProcessStats = $validator->getParameter('includeProcessStats')->isNumber()->setDefault(1)->getValue();
        $status = (new \BO\Zmsbackend\Status\Service\Status())->readEntity(\App::$now, $includeProcessStats);
        $status['version'] = \BO\Zmsbackend\Helper\Version::getArray();
        if (\App::DEBUG) {
            $status['opcache'] = [
                'config' => opcache_get_configuration(),
                'status' => opcache_get_status(false)
            ];
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $status;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
