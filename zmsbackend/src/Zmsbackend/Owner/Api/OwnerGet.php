<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Owner\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Owner\Service\Owner as Query;

class OwnerGet extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = new \BO\Zmsbackend\Helper\User($request, 2);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $owner = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $owner->hasId()) {
            throw new \BO\Zmsbackend\Owner\Exception\OwnerNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);

        if ($workstation->hasRights()) {
            $workstation->checkPermissions('jurisdiction');
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
