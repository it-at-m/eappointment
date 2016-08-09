<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Session as Query;
use \BO\Mellon\Validator;

/**
 * Handle requests concerning services
 */
class SessionUpdate extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $sessionData = new \BO\Zmsentities\Session($input);
        $message->data = $query->updateEntity($sessionData);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
