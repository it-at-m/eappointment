<?php

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsdb\Request;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Department as DepartmentEntity;
use BO\Zmsentities\Collection\DepartmentList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OverallCalendarLoadData extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $scopeIds = $_GET['scopeIds'] ?? null;
        $dateFrom = $_GET['dateFrom'] ?? null;
        $dateUntil = $_GET['dateUntil'] ?? null;
        $updateAfter = $_GET['updateAfter'] ?? null;

        if (!$scopeIds || !$dateFrom || !$dateUntil) {
            throw new \Exception('Missing required parameters');
        }

        $params = [
            'scopeIds' => $scopeIds,
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
        ];
        if ($updateAfter) {
            $params['updateAfter'] = $updateAfter;
        }

        $apiResult = \App::$http->readGetResult('/overallcalendar/', $params);

        $body = (string)$apiResult->getResponse()->getBody();
        $response->getBody()->write($body);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
