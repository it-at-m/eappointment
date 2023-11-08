<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

class ScopeCalldisplayImageDataUpdate extends BaseController
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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $scope = (new Query)->readEntity($args['id']);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }(new Helper\User($request, 2))->checkRights(
            'scope',
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $input = Validator::input()->isJson()->getValue();
        $mimepart = new \BO\Zmsentities\Mimepart($input);

        $message = Response\Message::create($request);
        $message->data = (new Query)->writeImageData($scope->id, $mimepart);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
