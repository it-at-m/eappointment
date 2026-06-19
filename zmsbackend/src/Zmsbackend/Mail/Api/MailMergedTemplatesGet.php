<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Mail\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Mail\Service\MailTemplates as MailTemplatesQuery;

class MailMergedTemplatesGet extends \BO\Zmsbackend\Api\BaseController
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

        $providerId = $args['providerId'];

        $mailtemplates = (new MailTemplatesQuery())->readCustomizedListForProvider($providerId);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $mailtemplates;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
