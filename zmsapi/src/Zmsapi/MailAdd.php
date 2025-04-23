<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Mail as Query;
use BO\Mellon\Validator;

class MailAdd extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights('basic');

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Mail($input);
        $entity->testValid();

        $process = new \BO\Zmsentities\Process($entity->process);

        $mail = (new Query())->writeInQueue($entity, \App::$now);
        $message = Response\Message::create($request);
        $message->data = $mail;

        if ($process->shouldSendAdminMailOnClerkMail()) {
            (new \BO\Zmsdb\Mail())->writeInQueueWithAdmin($entity);
        }

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
