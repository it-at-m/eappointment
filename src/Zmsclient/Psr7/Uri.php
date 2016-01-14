<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Uri extends \Asika\Http\Uri\PsrUri implements \Psr\Http\Message\UriInterface
{
}
