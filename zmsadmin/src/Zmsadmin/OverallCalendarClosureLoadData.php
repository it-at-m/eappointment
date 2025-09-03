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
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $scopeIds  = $query['scopeIds']  ?? null;
        $dateFrom  = $query['dateFrom']  ?? null;
        $dateUntil = $query['dateUntil'] ?? null;

        if (!$scopeIds || !$dateFrom || !$dateUntil) {
            $error = [
                'error'   => true,
                'message' => 'Missing required parameters: scopeIds, dateFrom, dateUntil',
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

        $apiResult = \App::$http->readGetResult('/closure/', $params);
        $apiResp   = $apiResult->getResponse();

        $lastMod = $apiResp->getHeaderLine('Last-Modified');
        if ($lastMod !== '') {
            $response = $response->withHeader('Last-Modified', $lastMod);
        }
        $contentType = $apiResp->getHeaderLine('Content-Type');
        if ($contentType !== '') {
            $response = $response->withHeader('Content-Type', $contentType);
        } else {
            $response = $response->withHeader('Content-Type', 'application/json');
        }

        $bodyStream = $apiResp->getBody();
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }
        $rawBody = (string)$bodyStream;

        $response->getBody()->write($rawBody);

        return $response->withStatus($apiResp->getStatusCode());
    }
}
