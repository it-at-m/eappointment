<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Calendar as Query;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CalendarGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $query = new Query();
        $message = Response\Message::create($request);
        $fillWithEmptyDays = Validator::param('fillWithEmptyDays')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calendar = new \BO\Zmsentities\Calendar($input);
        if (!$calendar->hasFirstAndLastDay()) {
            throw new Exception\Calendar\InvalidFirstDay('First and last day are required');
        } else {
            $calendar = $query->readResolvedEntity($calendar, \App::getNow())->withLessData();
            if ($fillWithEmptyDays) {
                $calendar = $calendar->withFilledEmptyDays();
            }
            $message->data = $calendar;
        }
        if (0 == count($message->data['days'])) {
            $exception = new Exception\Calendar\AppointmentsMissed();
            $exception->data = $message->data;
            throw $exception;
        }
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
