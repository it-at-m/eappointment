<?php

namespace BO\Zmsclient\Psr7;

class RequestException extends \Exception
{
    /**
     * @var \Psr\Http\Message\RequestInterface $request
     */
    public $request;

    /**
     * @param string $message
     * @param \Psr\Http\Message\RequestInterface|null $request reference for better error messages
     * @param \Exception|null $previous
     */
    public function __construct(
        $message = '',
        \Psr\Http\Message\RequestInterface $request = null,
        \Exception $previous = null
    ) {
        $this->request = $request;
        $code = 0;
        parent::__construct($message, $code, $previous);
    }
}
