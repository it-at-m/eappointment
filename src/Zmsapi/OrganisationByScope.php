<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation as Query;
use \BO\Zmsdb\Scope;

/**
  * Handle requests concerning services
  */
class OrganisationByScope extends BaseController
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
        (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = (new Scope())->readEntity($args['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $organisation = (new Query())->readByScopeId($scope->id, $resolveReferences);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
