<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Calendar as Query;

/**
  * Handle requests concerning services
  *
  */
class CalendarGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        // TODO error if json is not valid
        $query = new Query();
        $calendar = new \BO\Zmsentities\Calendar($input);
        $message->data = $query->readResolvedEntity($calendar);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
