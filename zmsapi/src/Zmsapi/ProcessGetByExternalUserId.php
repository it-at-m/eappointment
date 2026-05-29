<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;

/**
 * Citizen-scoped process read for trusted backends (e.g. zmscitizenapi after JWT validation).
 * Requires workstation/service authentication via X-Authkey and matching external user id.
 */
class ProcessGetByExternalUserId extends BaseController
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
        (new Helper\User($request, 2))->checkRights();

        $resolveReferences = (int) (Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue() ?? 2);
        $processId = (int) $args['id'];
        $externalUserId = $args['externalUserId'];

        $process = (new Process())->readEntity(
            $processId,
            new \BO\Zmsdb\Helper\NoAuth(),
            $resolveReferences
        );

        if (!$process || !$process->hasId()) {
            $exception = new Exception\Process\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $this->validateExternalUserId($process, $externalUserId);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function validateExternalUserId(\BO\Zmsentities\Process $process, string $externalUserId): void
    {
        $processExternalUserId = $process->getExternalUserId();
        if (
            $processExternalUserId === null
            || $processExternalUserId === ''
            || (string) $processExternalUserId !== (string) $externalUserId
        ) {
            throw new Exception\Process\ExternalUserIdMatchFailed();
        }
    }
}
