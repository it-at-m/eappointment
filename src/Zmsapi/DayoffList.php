<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\DayOff as Query;

/**
  * Handle requests concerning services
  */
class DayoffList extends BaseController
{
    /**
     * @return String
     */
    public static function render($year)
    {
        $message = Response\Message::create(Render::$request);
        $dayOffList = (new Query())->readByYear($year);
        $message->data = $dayOffList;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
