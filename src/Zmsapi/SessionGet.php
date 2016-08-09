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
class SessionGet extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($sessionName, $sessionId)
    {
        $message = Response\Message::create(Render::$request);
        $session = (new Query())->readEntity($sessionName, $sessionId);
        $message->data = $session;
        // $message->data = \BO\Zmsentities\Session::createExample();
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
