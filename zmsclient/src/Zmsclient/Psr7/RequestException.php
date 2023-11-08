<?php

namespace BO\Zmsclient\Psr7;

class RequestException extends \Exception
{
    /**
     * @var \Psr\Http\Message\RequestInterface $request
     */
    public $request;

    /**
     * @param String $message
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request (optional) reference for better error messages
     * @param \Exception $previous
     */
    public function __construct(
        $message = '',
        \Psr\Http\Message\RequestInterface $request = null,
        \Exception $previous = null
    ) {
        $this->request = $request;
        $code = null;
        parent::__construct($message, $code, $previous);
    }
}
