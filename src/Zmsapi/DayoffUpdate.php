<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  */
class DayoffUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($year)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $list = array();
        foreach ($input as $dayoff) {
            $list[] = new \BO\Zmsentities\DayOff($dayoff);
        }
        $message->data = $list;
        $year = $year; // @todo update data
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
