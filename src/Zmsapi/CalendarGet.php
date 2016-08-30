<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Calendar as Query;

class CalendarGet extends BaseController
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
        if (null === $input) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->data = null;
        } else {
            $entity = new \BO\Zmsentities\Calendar($input);
            $message->data = $query->readResolvedEntity($entity, \App::getNow());
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
