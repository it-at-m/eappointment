<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process as Query;

/**
  * Handle requests concerning services
  */
class ProcessGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {

        $process = (new Query())->readEntity($itemId, $authKey, 1);
        $message = Response\Message::create();
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
