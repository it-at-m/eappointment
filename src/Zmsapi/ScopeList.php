<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class ScopeList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $scope = (new Query())->readList(1);
        $message = Response\Message::create(Render::$request);
        $message->data = $scope;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
