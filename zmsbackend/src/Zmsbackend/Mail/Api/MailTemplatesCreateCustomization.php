<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Mail\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

class MailTemplatesCreateCustomization extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('mailtemplates');

        $input = Validator::input()->isJson()->getValue();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Mail\Service\MailTemplates())->createCustomizationForProvider($input['providerId'], $input['templateName'], $input['templateContent']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
