<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\DayOff as Query;

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
        Helper\User::checkRights('superuser');

        $query = new Query();
        $input = Validator::input()->isJson()->getValue();
        $collection = new \BO\Zmsentities\Collection\DayoffList($input);
        $collection->hasDatesInYear($year);
        $collection = $query->writeCommonDayoffsByYear($input, $year);
        $message = Response\Message::create(Render::$request);
        $message->data = $collection;
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
