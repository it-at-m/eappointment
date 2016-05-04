<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  */
class WorkstationGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $message->data = \BO\Zmsentities\Workstation::createExample();
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
