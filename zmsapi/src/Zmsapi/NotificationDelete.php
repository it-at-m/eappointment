<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Notification as Query;

class NotificationDelete extends BaseController
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
        (new Helper\User($request))->checkRights('superuser');
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $query = new Query();
        $notification = $query->readEntity($args['id'], 2);
        if ($notification && ! $notification->hasId()) {
            throw new Exception\Notification\NotificationNotFound();
        }


        if (! $query->deleteEntity($notification->id)) {
            throw new Exception\Notification\NotificationDeleteFailed(); // @codeCoverageIgnore
        }
        $query->writeInCalculationTable($notification);

        $message = Response\Message::create($request);
        $message->data = $notification;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}
