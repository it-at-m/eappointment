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
class ScopeCalldisplayImageDataGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        Helper\User::checkRights('scope');
        $query = new Query();
        $scope = $query->readEntity($itemId)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $query->readImageData($itemId);

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
