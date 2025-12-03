<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use Psr\Http\Message\ServerRequestInterface;

class ProcessContextExtractor
{
    public static function extractProcessContext(ServerRequestInterface $request, ?string $responseBody): array
    {
        $process = [];

        // 1) Start with what the client sent (request body)
        $bodyData = $request->getParsedBody();
        if (is_array($bodyData)) {
            $process = self::buildProcessContextFromArray($bodyData);
        }

        // 2) Overwrite with what the API returns (response JSON), if any
        if ($responseBody !== null && $responseBody !== '') {
            $decoded = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fromResponse = self::buildProcessContextFromArray($decoded);
                if (!empty($fromResponse)) {
                    $process = array_replace($process, $fromResponse);
                }
            }
        }

        if (empty($process)) {
            return [];
        }

        return ['process' => $process];
    }

    private static function buildProcessContextFromArray(array $data): array
    {
        $process = [];

        self::addIntFieldIfPresent($process, 'processId', $data, 'processId');
        self::addIntFieldIfPresent($process, 'officeId', $data, 'officeId');

        $scopeId = self::extractScopeId($data);
        if ($scopeId !== null) {
            $process['scopeId'] = $scopeId;
        }

        self::addIntFieldIfPresent($process, 'serviceId', $data, 'serviceId');

        if (isset($data['displayNumber']) && $data['displayNumber'] !== '') {
            $process['displayNumber'] = (string)$data['displayNumber'];
        }

        if (isset($data['scope']['displayNumberPrefix']) && $data['scope']['displayNumberPrefix'] !== '' && isset($process['processId'])) {
            $prefix = (string)$data['scope']['displayNumberPrefix'];
            $process['processId'] = $prefix . $process['processId'];
        }

        return $process;
    }

    private static function addIntFieldIfPresent(array &$process, string $targetKey, array $source, string $sourceKey): void
    {
        if (isset($source[$sourceKey]) && is_numeric($source[$sourceKey])) {
            $process[$targetKey] = (int)$source[$sourceKey];
        }
    }

    private static function extractScopeId(array $data): ?int
    {
        if (isset($data['scope']['id']) && is_numeric($data['scope']['id'])) {
            return (int)$data['scope']['id'];
        }

        if (isset($data['scopeId']) && is_numeric($data['scopeId'])) {
            return (int)$data['scopeId'];
        }

        return null;
    }
}
