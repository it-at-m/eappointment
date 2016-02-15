<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Availability as Query;

/**
  * Handle requests concerning services
  *
  */
class AvailabilityGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {        
        $availability = (new Query())->readEntity($itemId);
        $message = Response\Message::create();
        $message->data = $availability;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
