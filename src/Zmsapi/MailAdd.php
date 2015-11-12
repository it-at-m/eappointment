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
class MailAdd extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $message->data = new \BO\Zmsentities\Mail($input);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
