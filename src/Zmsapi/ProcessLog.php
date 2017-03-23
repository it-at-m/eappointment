<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Log as Query;

/**
  * Handle requests concerning services
  */
class ProcessLog extends BaseController
{
    /**
     * @return String
     */
    public static function render($processId)
    {
        Helper\User::checkRights('superuser');
        $message = Response\Message::create(Render::$request);
        $logList = (new Query())->readByProcessId($processId);
        $message->data = $logList;

        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
