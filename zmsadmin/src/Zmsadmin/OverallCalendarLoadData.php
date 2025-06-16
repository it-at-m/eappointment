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
        $scopeIds    = $_GET['scopeIds']    ?? null;
        $dateFrom    = $_GET['dateFrom']    ?? null;
        $dateUntil   = $_GET['dateUntil']   ?? null;
        $updateAfter = $_GET['updateAfter'] ?? null;

        if ($scopeIds === null && $dateFrom === null && $dateUntil === null) {
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $missing = [];
        if (!$scopeIds) {
            $missing[] = 'scopeIds';
        }
        if (!$dateFrom) {
            $missing[] = 'dateFrom';
        }
        if (!$dateUntil) {
            $missing[] = 'dateUntil';
        }
        if (!empty($missing)) {
            $error = [
                'error'   => true,
                'message' => 'Missing required parameters: ' . implode(', ', $missing),
            ];
            $response->getBody()->write(json_encode($error));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        $params = [
            'scopeIds'  => $scopeIds,
            'dateFrom'  => $dateFrom,
            'dateUntil' => $dateUntil,
        ];
        if ($updateAfter) {
            $params['updateAfter'] = $updateAfter;
        }

        $apiResult = \App::$http->readGetResult('/overallcalendar/', $params);
        $rawBody   = (string)$apiResult->getResponse()->getBody();

        $response->getBody()->write($rawBody);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
