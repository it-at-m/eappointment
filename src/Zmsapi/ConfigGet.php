<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Config as Query;

class ConfigGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $token = Render::$request->getHeader('X-Token');
        if (\App::SECURE_TOKEN == current($token)) {
            $config = (new Query())->readEntity();
            if (!$config) {
                $message->meta->error = true;
                $message->statuscode = 404;
            } else {
                $message->data = $config;
                $message->data->id = 1;
            }
        } else {
            throw new Exception\Config\SecureTokenMissed();
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
