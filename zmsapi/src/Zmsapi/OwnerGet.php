<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Owner as Query;

class OwnerGet extends BaseController
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
        $workstation = new Helper\User($request, 2);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $owner = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $owner->hasId()) {
            throw new Exception\Owner\OwnerNotFound();
        }

        $message = Response\Message::create($request);

        if ($workstation->hasRights()) {
            $workstation->checkRights(
                new \BO\Zmsentities\Useraccount\EntityAccess($owner)
            );
        } else {
            $owner = $owner->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $owner;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
