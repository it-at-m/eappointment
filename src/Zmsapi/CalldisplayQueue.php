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
class CalldisplayQueue extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        Validator::input()->isJson()->getValue();
        $message->data = array(\BO\Zmsentities\Queue::createExample());
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
