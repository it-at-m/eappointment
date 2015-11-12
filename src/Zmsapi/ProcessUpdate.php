<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class ProcessUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $message->data = new \BO\Zmsentities\Process($input);
        $message->data->id = $itemId;
        $message->data->authKey = $authKey;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
