<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Mail as Query;
use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  */
class MailAdd extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();       
        $mail = new \BO\Zmsentities\Mail($input);        
        $message->data = $query->writeInMailQueue($mail);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
