<?php

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OverallCalendarClosureLoadData extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $scopeIds  = $_GET['scopeIds']  ?? null;
        $dateFrom  = $_GET['dateFrom']  ?? null;
        $dateUntil = $_GET['dateUntil'] ?? null;

        if (!$scopeIds || !$dateFrom || !$dateUntil) {
            $error = [
                'error'   => true,
                'message' => 'Missing required parameters: scopeIds, dateFrom, dateUntil',
            ];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $params = [
            'scopeIds'  => $scopeIds,
            'dateFrom'  => $dateFrom,
            'dateUntil' => $dateUntil,
        ];

        $apiResult = \App::$http->readGetResult('/closure/', $params);
        $apiResp   = $apiResult->getResponse();

        $lastMod = $apiResp->getHeaderLine('Last-Modified');
        if ($lastMod) {
            $response = $response->withHeader('Last-Modified', $lastMod);
        }

        $rawBody = (string)$apiResp->getBody();
        $response->getBody()->write($rawBody);

        return $response->withHeader('Content-Type', 'application/json');
    }
}