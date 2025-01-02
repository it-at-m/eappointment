<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\MapperService;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentUpdate extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!($request instanceof ServerRequestInterface)) {
            return $this->createJsonResponse(
                $response, 
                ['errors' => [ErrorMessages::get('invalidRequest')]], 
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }
        
        $body = $request->getParsedBody();

        $clientData = $this->extractClientData($body);
        
        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $reservedProcess = $this->getReservedProcess(
                $clientData->processId,
                $clientData->authKey
            );
            
            if (!empty($reservedProcess['errors'])) {
                return $this->createJsonResponse($response, $reservedProcess, 404);
            }

            $updatedProcess = $this->updateProcessWithClientData($reservedProcess, $clientData);
            
            $result = $this->saveProcessUpdate($updatedProcess);
            if (!empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $this->createJsonResponse($response, $result->toArray(), 200);

        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'processId' => isset($body['processId']) ? (int) $body['processId'] : 0,
            'authKey' => isset($body['authKey']) ? (string) $body['authKey'] : null,
            'familyName' => isset($body['familyName']) ? (string) $body['familyName'] : null,
            'email' => isset($body['email']) ? (string) $body['email'] : null,
            'telephone' => isset($body['telephone']) ? (string) $body['telephone'] : null,
            'customTextfield' => isset($body['customTextfield']) ? (string) $body['customTextfield'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateUpdateAppointmentInputs(
            $data->processId,
            $data->authKey,
            $data->familyName,
            $data->email,
            $data->telephone,
            $data->customTextfield
        );
    }

    private function getReservedProcess(int $processId, string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }

    private function updateProcessWithClientData(ThinnedProcess $process, object $data): ThinnedProcess
    {
        $process->familyName = $data->familyName ?? $process->familyName ?? null;
        $process->email = $data->email ?? $process->email ?? null;
        $process->telephone = $data->telephone ?? $process->telephone ?? null;
        $process->customTextfield = $data->customTextfield ?? $process->customTextfield ?? null;

        return $process;
    }

    private function saveProcessUpdate(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::updateClientData($processEntity);
        
        if (!empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }
}