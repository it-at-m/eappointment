<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
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
        $availabilities = (new Query())->readList($scopeId, 1);
        $message = Response\Message::create();
        $message->data = $availabilities;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
