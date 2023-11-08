<?php

namespace BO\Zmsclient;

class Exception extends \Exception
{
    /**
     * @var \Psr\Http\Message\ResponseInterface $response
     */
    public $response;

    /**
     * @var \Psr\Http\Message\RequestInterface $request
     */
    public $request;

    /**
     * @var String $template for rendering exception
     *
     */
    public $template = 'bo/zmsclient/exception';

    /**
     * @var Mixed $data for rendering exception
     *
     */
    public $data = [];

    /**
     * @var Mixed $trace Code trace
     *
     */
    public $trace;

    /**
     * @param String $message
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request (optional) reference for better error messages
     * @param \Exception $previous
     */
    public function __construct(
        $message = '',
        \Psr\Http\Message\ResponseInterface $response = null,
        \Psr\Http\Message\RequestInterface $request = null,
        \Exception $previous = null
    ) {
        $this->response = $response;
        $this->request = $request;
        $code = $this->code ?? null;
        if (null !== $response) {
            $code = $response->getStatusCode();
        }
        if (null !== $request) {
            $info = $this->getRequestInfoString();
            $message .= " $info";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Info about request intended for error messages
     *
     * @return String
     */
    protected function getRequestInfoString()
    {
        $info = '';
        if (null !== $this->request) {
            $uri = $this->request->getRequestTarget();
            $protocol = $this->request->getProtocolVersion();
            $method = $this->request->getMethod();
            $info = "($method $uri HTTP/$protocol)";
        }
        return $info;
    }
}
