<?php
namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\OverallCalendar as CalendarQuery;
use DateTimeImmutable;

class OverallCalendarRead extends BaseController
{

    public function readResponse(
        \Psr\Http\Message\RequestInterface  $request,
        \Psr\Http\Message\ResponseInterface $response,
        array                               $args
    ) {
        $scopeIdCsv = Validator::param('scopeIds')
            ->isString()
            ->isMatchOf('/^\d+(,\d+)*$/')
            ->assertValid()
            ->getValue();

        $scopeIds = array_map('intval', explode(',', $scopeIdCsv));
        if (empty($scopeIds)) {
            $msg = Response\Message::create($request);
            $msg->meta->error = true;
            $msg->meta->message = 'Invalid parameter: scopeIds';
            $msg->statuscode = 400;

            return Render::withJson(
                $response,
                $msg->setUpdatedMetaData(),
                $msg->getStatuscode()
            );
        }

        $dateFrom = Validator::param('dateFrom')
            ->isDate('Y-m-d')                            // ValidDate
            ->assertValid()
            ->getValue();

        $dateUntil = Validator::param('dateUntil')
            ->isDate('Y-m-d')
            ->assertValid()
            ->getValue();

        $updateAfter = Validator::param('updateAfter')
            ->isDatetime()
            ->setDefault(null)
            ->getValue();

        $rows = (new CalendarQuery())->readSlots(
            $scopeIds,
            $dateFrom,
            $dateUntil,
            $updateAfter
        );

        $msg           = Response\Message::create($request);
        $msg->data     = $rows;
        $msg->meta->rows = count($rows);

        $response = Render::withLastModified(
            $response,
            (new DateTimeImmutable())->getTimestamp(),
            '0'
        );
        return Render::withJson(
            $response,
            $msg->setUpdatedMetaData(),
            $msg->getStatuscode()
        );
    }
}
