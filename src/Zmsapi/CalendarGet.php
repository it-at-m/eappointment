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
        $calendarData = $query->readResolvedEntity($calendar, \App::getNow());
        if (null === $input) {
            throw new Exception\InvalidInput();
        } elseif (!isset($calendar['firstDay']) || !isset($calendar['lastDay'])) {
            throw new Exception\Calendar\InvalidFirstDay();
        } elseif (0 == count($calendar['days'])) {
            throw new Exception\Calendar\AppointmentsMissed();
        } else {
            $message->data = $calendarData;
        }
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
