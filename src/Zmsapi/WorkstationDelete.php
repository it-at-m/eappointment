<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class WorkstationDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginname)
    {
        $status = 200;
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $workstation = $query->readUpdatedLogoutEntity($loginname);
        $xAuthKey = Render::$request->getHeader('X-AuthKey');
        if (!current($xAuthKey)) {
            $status = 401;
        }
        if (null === $workstation) {
            $status = 404;
        }
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message, $status);
    }
}
