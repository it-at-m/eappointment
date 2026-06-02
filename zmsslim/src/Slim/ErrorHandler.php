<?php

/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *
 */

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorHandler
{
    /**
     * @var array<string, mixed>
     */
    public $session = array();

    /**
     * @var \Psr\Http\Message\RequestInterface|null $request
     *
     */
    public $request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface|null $response
     *
     */
    public $response = null;


    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function check()
    {
        foreach (func_get_args() as $errorCheck) {
            if ($redirect = $errorCheck($this)) {
                if (isset($redirect['queryParams'])) {
                    return Render::redirect($redirect['route'], array(), $redirect['queryParams']);
                }
                return Render::redirect($redirect['route'], $redirect['params']);
            }
        }
        return false;
    }
}
