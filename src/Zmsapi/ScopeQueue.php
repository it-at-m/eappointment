<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class ScopeQueue extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = $query->readEntity($itemId, $resolveReferences)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $message->data = $query->readWithWaitingTime($itemId, \App::$now)->withPickupDestination($scope);

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
