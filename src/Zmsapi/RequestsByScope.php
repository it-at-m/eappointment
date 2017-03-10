<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Request as Query;

/**
  * Handle requests concerning services
  */
class RequestsByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId)
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        $scope = (new \BO\Zmsdb\Scope)->readEntity($scopeId, $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $requestList = $query
            ->readListByProvider($scope->provider['source'], $scope->getProviderId(), $resolveReferences - 1);

        $message = Response\Message::create(Render::$request);
        $message->data = $requestList;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
