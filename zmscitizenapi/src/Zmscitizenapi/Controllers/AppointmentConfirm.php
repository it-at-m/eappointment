<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
* Controller for handling appointment confirmations
* 
* @package BO\Zmscitizenapi\Controllers
*/
class AppointmentConfirm extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $error = ErrorMessages::get('notImplemented');
        return $this->createJsonResponse($response, $error, $error['statusCode']);
    }
}
