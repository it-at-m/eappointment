<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use Slim\Psr7\Factory\StreamFactory;

/**
 * Delete availability, API proxy
 *
 */
class AvailabilityDelete extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $result = \App::$http->readDeleteResult('/availability/' . $entityId . '/');
        $apiResponse = $result->getResponse();
        $body = (string) $apiResponse->getBody();
        $stream = (new StreamFactory())->createStream($body);
        return $apiResponse
            ->withBody($stream)
            ->withoutHeader('Transfer-Encoding')
            ->withoutHeader('Content-Length')
            ->withHeader('Content-Length', (string) strlen($body));
    }
}
