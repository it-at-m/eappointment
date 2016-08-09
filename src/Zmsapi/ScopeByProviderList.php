<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class ScopeByProviderList extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = (new Query())->readByProviderId($itemId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $scope;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
