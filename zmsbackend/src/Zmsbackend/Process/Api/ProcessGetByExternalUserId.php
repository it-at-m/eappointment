<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;

/**
 * Citizen-scoped process read for trusted backends (e.g. zmscitizenapi after JWT validation).
 * Requires workstation/service authentication via X-Authkey and matching external user id.
 */
class ProcessGetByExternalUserId extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request, 2))->checkRights();

        $resolveReferences = (int) (Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue() ?? 2);
        $processId = (int) $args['id'];
        $externalUserId = $args['externalUserId'];

        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity(
            $processId,
            new \BO\Zmsbackend\Helper\NoAuth(),
            $resolveReferences
        );

        if (!$process || !$process->hasId()) {
            $exception = new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $this->validateExternalUserId($process, $externalUserId);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
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
            throw new \BO\Zmsbackend\Process\Exception\ExternalUserIdMatchFailed();
        }
    }
}
