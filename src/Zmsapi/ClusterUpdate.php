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
class ClusterUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $message->data = new \BO\Zmsentities\Cluster($input);
        $message->data->id = $itemId;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
