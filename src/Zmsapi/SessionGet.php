<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Session as Query;
use \BO\Mellon\Validator;
use \BO\Zmsclient\SessionHandler;

/**
 * Handle requests concerning services
 */
class SessionGet extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();

        $handler = new SessionHandler();
        session_set_save_handler($handler, true);
        if ($input && array_key_exists('id', $input)) {
            session_id($input['id']);
            session_name($input['name']);
        } else {
            session_name(\App::SESSION_NAME);
        }

        session_start();
        $session = (new Query())->readEntity();
        $message->data = $session;
        // $message->data = \BO\Zmsentities\Session::createExample();
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
