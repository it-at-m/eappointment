<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  */
class RequestList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $message->data = array(\BO\Zmsentities\Request::createExample());
        Render::json($message);
    }
}
