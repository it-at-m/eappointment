<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Owner as Query;

/**
 * Delete an owner by Id
 */
class OwnerDelete extends BaseController
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
        $query = new Query();
        $owner = $query->readEntity($args['id']);
        if (! $owner->hasId()) {
            throw new Exception\Owner\OwnerNotFound();
        }
        $query->deleteEntity($owner->id);

        $message = Response\Message::create($request);
        $message->data = $owner;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
