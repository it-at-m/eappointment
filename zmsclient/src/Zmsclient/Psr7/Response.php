<?php

namespace BO\Zmsclient\Psr7;

use BO\Slim\Response as SlimResponse;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Response extends SlimResponse implements \Psr\Http\Message\ResponseInterface
{
}
