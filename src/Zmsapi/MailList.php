<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Mail as Query;

/**
  * Handle requests concerning services
  */
class MailList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $mailList = (new Query())->readList(1);
        $message = Response\Message::create(Render::$request);
        $message->data = $mailList;
        Render::json($message);
    }
}
