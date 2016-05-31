<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Owner as Query;

/**
  * Handle requests concerning services
  */
class OwnerGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $owner = (new Query())->readEntity($itemId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $owner;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
