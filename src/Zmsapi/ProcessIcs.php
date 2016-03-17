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
        // @codingStandardsIgnoreStart
        $message->data = (new Notification\IcsAppointment())->createIcsString($process);
        // @codingStandardsIgnoreEnd
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
