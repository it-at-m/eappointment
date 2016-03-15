<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

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
        $message = Response\Message::create();
        $year = $year; // @todo fetch data
        $message->data = array(\BO\Zmsentities\Dayoff::createExample());
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
