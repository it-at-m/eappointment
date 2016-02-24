<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Session as Query;

/**
 * Handle requests concerning services
 */
class SessionHandler extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render()
    {
        $session = (new Query())->readEntity();
        $message = Response\Message::create();
        $message->data = $session;
        //$message->data = \BO\Zmsentities\Session::createExample();
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
