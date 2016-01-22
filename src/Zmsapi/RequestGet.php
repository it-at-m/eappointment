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
class RequestGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $itemId)
    {
        $message = Response\Message::create();
        $message->data = \BO\Zmsentities\Request::createExample();
        $message->data->id = $itemId;
        $message->data->source = $source;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
