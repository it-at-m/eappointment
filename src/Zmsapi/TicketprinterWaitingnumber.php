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
class TicketprinterWaitingnumber extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId)
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $scopeId = $scopeId; // @todo fetch data
        $input = $input;
        $message->data = array(\BO\Zmsentities\Process::createExample());
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
