<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Mail\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Mail\Service\Mail as Query;

class MailGet extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('superuser');

        $mailId = $args['id'];
        $mail = (new Query())->readEntity($mailId);

        if (!$mail || !$mail->hasId()) {
            throw new \BO\Zmsbackend\Mail\Exception\MailNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $mail;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
