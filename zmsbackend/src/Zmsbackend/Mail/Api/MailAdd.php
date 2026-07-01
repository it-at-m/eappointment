<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Mail\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Mail\Service\Mail as Query;
use BO\Mellon\Validator;

class MailAdd extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkRights('basic');

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Mail($input);
        $entity->testValid();

        $process = new \BO\Zmsentities\Process($entity->process);

        $mail = (new Query())->writeInQueue($entity, \App::$now);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $mail;

        if ($process->shouldSendAdminMailOnClerkMail()) {
            (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueueWithAdmin($entity);
        }

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
