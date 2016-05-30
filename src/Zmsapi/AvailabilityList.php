<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

/**
  * Handle requests concerning services
  */
class AvailabilityList extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $availabilities = (new Query())->readList($scopeId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $availabilities;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
