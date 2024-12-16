<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use \BO\Zmscitizenapi\Services\ValidationService;
use \BO\Zmscitizenapi\Helper\UtilityHelper;
use BO\Zmscitizenapi\Services\ExceptionService;
use \BO\Zmsentities\Client;
use \BO\Zmsentities\Process;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentUpdate extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;

        $body = $request->getParsedBody();
        $processId = $body['processId'] ?? null;
        $authKey = $body['authKey'] ?? null;
        $familyName = $body['familyName'] ?? null;
        $email = $body['email'] ?? null;
        $telephone = $body['telephone'] ?? null;
        $customTextfield = $body['customTextfield'] ?? null;

        $errors = ValidationService::validateUpdateAppointmentInputs($processId, $authKey, $familyName, $email, $telephone, $customTextfield);
        if (!empty($errors['errors'])) {
            return $this->createJsonResponse($response, $errors, 400);
        }

        try {
            $reservedProcess = ZmsApiFacadeService::getProcessById($processId, $authKey);
            if (!empty($reservedProcess['errors'])) {
                return $this->createJsonResponse($response, $reservedProcess, 404);
            }

            $processEntity = new Process();
            $processEntity->id = $reservedProcess['data']['processId'] ?? null;
            $processEntity->authKey = $reservedProcess['data']['authKey'] ?? null;
            $processEntity->appointments = $reservedProcess['data']['appointments'] ?? [];
            $processEntity->clients = [];
            $client = new \stdClass();
            $client->familyName = $familyName ?? $reservedProcess['data']['familyName'] ?? null;
            $client->email = $email ?? $reservedProcess['data']['email'] ?? null;
            $client->telephone = $telephone ?? $reservedProcess['data']['telephone'] ?? null;
            $client->customTextfield = $customTextfield ?? $reservedProcess['data']['customTextfield'] ?? null;
            $processEntity->clients[0] = $client;
            $processEntity->scope = $reservedProcess['data']['scope'] ?? null;
            $processEntity->lastChange = $reservedProcess['data']['lastChange'] ?? time();

            if (isset($reservedProcess['data']['queue'])) {
                $processEntity->queue = $reservedProcess['data']['queue'];
            }
        
            $processEntity->createIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $processEntity->createTimestamp = time();

            
            $updatedProcess = ZmsApiFacadeService::updateClientData($processEntity);

            if (isset($updatedProcess['error']) && $updatedProcess['error'] === 'tooManyAppointmentsWithSameMail') {
                return $this->createJsonResponse($response, ExceptionService::tooManyAppointmentsWithSameMail(), 406);
            }

            $thinnedProcessData = UtilityHelper::getThinnedProcessData($updatedProcess);
            return $this->createJsonResponse($response, $thinnedProcessData, 200);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}