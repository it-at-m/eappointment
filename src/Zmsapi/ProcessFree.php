<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

/**
 * Handle requests concerning services
 */
class ProcessFree extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $query = new Query();
        $calendar = new \BO\Zmsentities\Calendar($input);
        $message->data = $query->readFreeAppointments($calendar);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
