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
        $request = $request instanceof ServerRequestInterface ? $request : null;
    
        $body = $request->getParsedBody();
        $processId = $body['processId'] ?? null;
        $authKey = $body['authKey'] ?? null;
        $familyName = $body['familyName'] ?? null;
        $email = $body['email'] ?? null;
        $telephone = $body['telephone'] ?? null;
        $customTextfield = $body['customTextfield'] ?? null;
    
        $errors = ValidationService::validateUpdateAppointmentInputs(
            isset($processId) ? (int) $processId : 0,
            isset($authKey) ? (string) $authKey : null,
            isset($familyName) ? (string) $familyName : null,
            isset($email) ? (string) $email : null,
            isset($telephone) ? (string) $telephone : null,
            isset($customTextfield) ? (string) $customTextfield : null
        );
        
        if (!empty($errors['errors'])) {
            return $this->createJsonResponse($response, $errors, 400);
        }
    
        try {
            $reservedProcess = new ThinnedProcess();
            $reservedProcess = ZmsApiFacadeService::getThinnedProcessById((int)$processId, $authKey);
            if (!empty($reservedProcess['errors'])) {
                return $this->createJsonResponse($response, $reservedProcess, 404);
            }
    
            $reservedProcess->familyName = $familyName ?? $reservedProcess->familyName ?? null;
            $reservedProcess->email = $email ?? $reservedProcess->email ?? null;
            $reservedProcess->telephone = $telephone ?? $reservedProcess->telephone ?? null;
            $reservedProcess->customTextfield = $customTextfield ?? $reservedProcess->customTextfield ?? null;
    
            $processEntity = MapperService::thinnedProcessToProcess($reservedProcess);
    
            $result = ZmsApiFacadeService::updateClientData($processEntity);       
            if (!empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            $thinnedProcess = MapperService::processToThinnedProcess($result);
            return $this->createJsonResponse($response, $thinnedProcess->toArray(), 200);
    
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
}