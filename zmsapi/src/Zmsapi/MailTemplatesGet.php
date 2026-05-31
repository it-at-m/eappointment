<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\MailTemplates as MailTemplatesQuery;
use BO\Zmsapi\Helper\User;

class MailTemplatesGet extends BaseController
{
    /**
     * @SuppressWarnings (Param)
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        (new Helper\User($request))->checkPermissions('mailtemplates');

        $config = (new MailTemplatesQuery())->readListWithoutProvider();

        $message = Response\Message::create($request);
        $message->data = $config;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
