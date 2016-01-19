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
        $message->data = new \BO\Zmsentities\Calendar($input);
        $message->data = \BO\Zmsentities\Calendar::createExample();
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
