<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;

class StatusLocktimeout extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @codeCoverageIgnore
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        throw new \BO\Zmsdb\Exception\Pdo\LockTimeout();
    }
}
