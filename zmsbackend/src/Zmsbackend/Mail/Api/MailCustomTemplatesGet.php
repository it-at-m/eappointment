<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Mail\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Mail\Service\MailTemplates as MailTemplatesQuery;
use BO\Zmsbackend\Helper\User;

class MailCustomTemplatesGet extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('mailtemplates');

        $providerId = $args['providerId'];

        $config = (new MailTemplatesQuery())->readListByProvider($providerId);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $config;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
