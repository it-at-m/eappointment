<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

class MailTemplatesUpdate extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        (new Helper\User($request))->checkPermissions('mailtemplates');

        $input = Validator::input()->isJson()->getValue();

        $message = Response\Message::create($request);
        $message->data = (new \BO\Zmsdb\MailTemplates())->updateTemplateContentById($input['templateId'], $input['templateContent']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
