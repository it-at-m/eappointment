<?php

namespace BO\Zmsclient\Psr7;

interface ClientInterface
{
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function readResponse(\Psr\Http\Message\RequestInterface $request);
}
