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
class ProcessDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $message = Response\Message::create();
        $message->data = \BO\Zmsentities\Process::createExample();
        $message->data->id = $itemId;
        $message->data->authKey = $authKey;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
