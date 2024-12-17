<?php

namespace BO\Zmscitizenapi\Controllers;

use \BO\Zmscitizenapi\BaseController;
use \BO\Zmscitizenapi\Helper\UtilityHelper;
use \BO\Zmscitizenapi\Models\ThinnedProcess;
use \BO\Zmscitizenapi\Services\ExceptionService;
use \BO\Zmscitizenapi\Services\ValidationService;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
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
            $reservedProcess = new ThinnedProcess();
            $reservedProcess = ZmsApiFacadeService::getThinnedProcessById($processId, $authKey);
            if (!empty($reservedProcess['errors'])) {
                return $this->createJsonResponse($response, $reservedProcess, 404);
            }
    
            $reservedProcess->familyName = $familyName ?? $reservedProcess->familyName ?? null;
            $reservedProcess->email = $email ?? $reservedProcess->email ?? null;
            $reservedProcess->telephone = $telephone ?? $reservedProcess->telephone ?? null;
            $reservedProcess->customTextfield = $customTextfield ?? $reservedProcess->customTextfield ?? null;
    
            $processEntity = UtilityHelper::thinnedProcessToProcess($reservedProcess);
    
            $updatedProcess = ZmsApiFacadeService::updateClientData($processEntity);
    
            if (isset($updatedProcess['error']) && $updatedProcess['error'] === 'tooManyAppointmentsWithSameMail') {
                return $this->createJsonResponse($response, ExceptionService::tooManyAppointmentsWithSameMail(), 406);
            }
    
            $thinnedProcess = UtilityHelper::processToThinnedProcess($updatedProcess);
            return $this->createJsonResponse($response, $thinnedProcess->toArray(), 200);
    
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
}