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
  */
class OwnerUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $message->data = new \BO\Zmsentities\Owner($input);
        $message->data->id = $itemId;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
