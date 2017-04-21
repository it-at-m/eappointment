<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Response extends \Slim\Http\Response implements \Psr\Http\Message\ResponseInterface
{
}
