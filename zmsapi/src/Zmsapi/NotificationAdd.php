<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Notification as Query;
use BO\Mellon\Validator;

class NotificationAdd extends BaseController
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
        (new Helper\User($request))->checkRights('sms');

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Notification($input);
        $entity->testValid();
        $notification = (new Query())->writeInQueue($entity, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $notification;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
