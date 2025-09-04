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
        $uriQuery = $request->getUri()->getQuery();
        if ($uriQuery !== '') {
            parse_str($uriQuery, $query);
        }

        $scopeIds  = $query['scopeIds']  ?? ($_GET['scopeIds']  ?? null);
        $dateFrom  = $query['dateFrom']  ?? ($_GET['dateFrom']  ?? null);
        $dateUntil = $query['dateUntil'] ?? ($_GET['dateUntil'] ?? null);

        if ($scopeIds === null && $dateFrom === null && $dateUntil === null) {
            $response->getBody()->write(json_encode([]));
            return $response->withHeader('Content-Type', 'application/json');
        }

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
        $response = $response->withHeader(
            'Content-Type',
            $contentType !== '' ? $contentType : 'application/json'
        );

        $bodyStream = $apiResp->getBody();
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }
        $rawBody = (string) $bodyStream;

        $response->getBody()->write($rawBody);

        return $response->withStatus($apiResp->getStatusCode());
    }
}
