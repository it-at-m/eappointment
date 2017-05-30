<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Scope;

class ScopeDelete extends BaseController
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
        (new Helper\User($request))->checkRights('scope');
        $scope = (new Scope)->readEntity($args['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $message = Response\Message::create($request);
        $message->data = (new Scope)->deleteEntity($scope->id);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
