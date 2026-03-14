<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;

class ProcessGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $processId = $args['id'] ?? null;
        $authKey = $args['authKey'] ?? '';
        error_log('[ProcessGet] entry processId=' . $processId . ' authKeyLength=' . strlen((string) $authKey));

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $this->testProcessData($processId, $authKey);
        error_log('[ProcessGet] testProcessData passed');

        $message = Response\Message::create($request);
        error_log('[ProcessGet] about to readEntity');
        $message->data = (new Process())->readEntity($processId, $authKey, $resolveReferences);
        error_log('[ProcessGet] readEntity done dataPresent=' . ($message->data !== null ? '1' : '0'));

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($processId, $authKey)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($processId);
        if (! $authCheck) {
            error_log('[ProcessGet] testProcessData: readAuthKeyByProcessId returned false/empty');
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $authKey && $authCheck['authName'] != $authKey) {
            error_log('[ProcessGet] testProcessData: authKey/authName mismatch');
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
