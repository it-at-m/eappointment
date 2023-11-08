<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Owner as Query;

class OwnerUpdate extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Owner($input);
        $entity->testValid();
        $owner = (new Query())->readEntity($args['id']);
        if (! $owner->hasId()) {
            throw new Exception\Owner\OwnerNotFound();
        }(new Helper\User($request, 2))->checkRights(
            new \BO\Zmsentities\Useraccount\EntityAccess($owner)
        );

        $message = Response\Message::create($request);
        $message->data = (new Query)->updateEntity($owner->id, $entity);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
