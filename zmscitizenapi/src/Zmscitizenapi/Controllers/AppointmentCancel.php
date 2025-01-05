<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\MapperService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentCancel extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerPostRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $body = $request->getParsedBody();
        $clientData = $this->extractClientData($body);

        $errors = $this->validateClientData($clientData);
        if (is_array($errors) && !empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            $process = $this->getProcess(
                $clientData->processId,
                $clientData->authKey
            );

            if (is_array($process) && !empty($process['errors'])) {
                return $this->createJsonResponse($response, $process, 404);
            }

            if (!$this->canBeCancelled($process)) {
                return $this->createJsonResponse(
                    $response,
                    ['errors' => [ErrorMessages::get('appointmentCanNotBeCanceled')]],
                    404
                );
            }

            // Send cancellation email before cancelling the appointment
            $this->sendCancellationEmail($process);

            $result = $this->cancelProcess($process);
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof ThinnedProcess
                ? $this->createJsonResponse($response, $result->toArray(), 200)
                : $this->createJsonResponse(
                    $response,
                    ErrorMessages::get('invalidRequest'),
                    ErrorMessages::get('invalidRequest')['statusCode']
                );

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
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetProcessById(
            $data->processId,
            $data->authKey
        );
    }

    private function getProcess(int $processId, string $authKey): ThinnedProcess|array
    {
        return ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
    }

    private function canBeCancelled(ThinnedProcess $process): bool
    {
        $timestamp = (int) $process->timestamp;
        return $timestamp > time();
    }

    private function cancelProcess(ThinnedProcess $process): ThinnedProcess|array
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        $result = ZmsApiFacadeService::cancelAppointment($processEntity);

        if (is_array($result) && !empty($result['errors'])) {
            return $result;
        }

        return MapperService::processToThinnedProcess($result);
    }

    private function sendCancellationEmail(ThinnedProcess $process): void
    {
        $processEntity = MapperService::thinnedProcessToProcess($process);
        ZmsApiFacadeService::sendCancelationEmail($processEntity);
    }
}