<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class StatusGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $message->data = \BO\Zmsentities\Status::createExample();
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
