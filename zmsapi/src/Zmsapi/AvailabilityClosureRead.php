<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Closure as ClosureQuery;
use DateTimeImmutable;

class AvailabilityClosureRead extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights('useraccount');

        $scopeIdCsv = Validator::param('scopeIds')
            ->isString()->isMatchOf('/^\d+(,\d+)*$/')->assertValid()->getValue();
        $scopeIds = array_values(array_unique(array_map('intval', explode(',', $scopeIdCsv))));

        $dateFrom  = Validator::param('dateFrom')->isDate('Y-m-d')->assertValid()->getValue();
        $dateUntil = Validator::param('dateUntil')->isDate('Y-m-d')->assertValid()->getValue();

        $dtFrom  = new DateTimeImmutable($dateFrom);
        $dtUntil = new DateTimeImmutable($dateUntil);
        if ($dtFrom > $dtUntil) {
            $payload = ['error' => true, 'message' => 'dateFrom must be before or equal to dateUntil'];
            return Render::withJson($response->withStatus(400), $payload, 400);
        }

        $items = (new ClosureQuery())->readByScopesInRange(
            $scopeIds,
            $dtFrom,
            $dtUntil
        );

        $msg       = Response\Message::create($request);
        $msg->data = ['items' => $items];

        $lastModified = (new DateTimeImmutable())->getTimestamp();
        $response = Render::withLastModified($response, $lastModified, '0');

        return Render::withJson($response, $msg->setUpdatedMetaData(), $msg->getStatuscode());
    }
}
