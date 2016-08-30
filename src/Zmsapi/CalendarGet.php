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
        $calendar = new \BO\Zmsentities\Calendar($input);
        if (null === $input) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->data = null;
        } elseif (!isset($calendar['firstDay']) || !isset($calendar['lastDay'])) {
            $message->meta->error = true;
            $message->meta->message = "Es wurde kein Startdatum definiert.";
            $message->meta->exception = "BO/Zmsapi/Exception/InvalidFirstDay";
        } else {
            $message->data = $query->readResolvedEntity($calendar, \App::getNow());
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
