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
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Mail($input);
        $mail = (new Query())->writeInMailQueue($entity);
        $message->data = $mail;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
