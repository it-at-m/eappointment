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
class SessionDelete extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($sessionName, $sessionId)
    {
        $session = (new Query())->readEntity($sessionName, $sessionId);
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $query->deleteEntity($sessionName, $sessionId);
        $message->data = $session;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
