<?php
/**
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process as Query;

/**
  * Handle requests concerning services
  */
class ProcessIcs extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $message = Response\Message::create();
        $process = (new Query())->readEntity($itemId, $authKey, 2);
        $ics = (new Messaging\IcsAppointment())->createIcsString($process);
        $message->data = $ics;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
