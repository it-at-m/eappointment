<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Status as Query;

/**
  * Handle requests concerning services
  */
class StatusGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $status = (new Query())->readEntity();
        $status['version']['major'] = \App::VERSION_MAJOR;
        $status['version']['minor'] = \App::VERSION_MINOR;
        $status['version']['patch'] = \App::VERSION_PATCH;
        $message = Response\Message::create();
        $message->data = $status;
        //throw new \Exception("Test");
        Render::lastModified(time(), '10');
        Render::json($message);
    }
}
