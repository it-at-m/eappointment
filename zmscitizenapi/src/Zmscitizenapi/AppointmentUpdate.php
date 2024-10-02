<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Helper\UtilityHelper;
use BO\Zmscitizenapi\Services\ExceptionService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentUpdate extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
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

            if (!$reservedProcess) {
                return $this->createJsonResponse($response, ExceptionService::exceptionAppointmentNotFound(), 404);
            }

            $reservedProcess['clients'][0]['familyName'] = $familyName;
            $reservedProcess['clients'][0]['email'] = $email;
            $reservedProcess['clients'][0]['telephone'] = $telephone;
            $reservedProcess['customTextfield'] = $customTextfield;

            $updatedProcess = ZmsApiFacadeService::updateClientData($reservedProcess);

            if (isset($updatedProcess['exception']) && $updatedProcess['exception'] === 'tooManyAppointmentsWithSameMail') {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'tooManyAppointmentsWithSameMail',
                    'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.',
                    'lastModified' => time()
                ], 406);
            }

            $thinnedProcessData = UtilityHelper::getThinnedProcessData($updatedProcess);
            return $this->createJsonResponse($response, $thinnedProcessData, 200);

        } catch (\Exception $e) {
            return [
                'errorCode' => 'unexpectedError',
                'errorMessage' => 'Unexpected error: ' . $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}