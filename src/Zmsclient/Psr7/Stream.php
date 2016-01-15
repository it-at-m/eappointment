<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Stream extends \Asika\Http\Stream\StringStream implements \Psr\Http\Message\StreamInterface
{
}
