<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;
use BO\Zmsbackend\Scope\Service\Scope;

/**
  * Handle requests concerning services
  */
class OrganisationByScope extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 0);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $organisation = (new Query())->readByScopeId($scope->id, $resolveReferences);

        if (! $organisation->hasId()) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        if ((new \BO\Zmsbackend\Helper\User($request))->hasRights()) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
