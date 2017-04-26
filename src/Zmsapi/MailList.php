<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
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
        Helper\User::checkRights('superuser');
        
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $mailList = (new Query())->readList($resolveReferences);

        if (0 < count($mailList)) {
            $message->data = $mailList;
        } else {
            $message->data = new \BO\Zmsentities\Collection\MailList();
            $message->error = false;
            $message->message = '';
        }
        Render::json($message, 200);
    }
}
