<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Config as Query;
use \BO\Zmsapi\Helper\User;

class ConfigGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        try {
            Helper\User::checkRights('basic');
        } catch (\Exception $exception) {
            $token = Render::$request->getHeader('X-Token');
            if (\App::SECURE_TOKEN != current($token)) {
                throw new Exception\Config\ConfigAuthentificationFailed();
            }
        }

        $config = (new Query())->readEntity();
        if (!$config) {
            throw new Exception\Config\ConfigNotFound();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $config;
        $message->data->id = 1;

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
