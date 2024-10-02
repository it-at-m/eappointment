<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Helper\UtilityHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentUpdate extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // Ensure the request is of the correct type
        $request = $request instanceof ServerRequestInterface ? $request : null;

        // Extract body parameters
        $body = $request->getParsedBody();
        $processId = $body['processId'] ?? null;
        $authKey = $body['authKey'] ?? null;
        $familyName = $body['familyName'] ?? null;
        $email = $body['email'] ?? null;
        $telephone = $body['telephone'] ?? null;
        $customTextfield = $body['customTextfield'] ?? null;

        // Validate input data (you can call a custom validation method here if needed)
        $errors = ValidationService::validateUpdateAppointmentInputs($processId, $authKey, $familyName, $email, $telephone, $customTextfield);
        if (!empty($errors['errors'])) {
            return $this->createJsonResponse($response, $errors, 400);
        }

        

        try {
            // Fetch the reserved process by ID and authKey
            $reservedProcess = ZmsApiFacadeService::getProcessById($processId, $authKey);

            //error_log(json_encode($reservedProcess['data']['scope']));
            //exit();

            if (!$reservedProcess) {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'appointmentNotFound',
                    'errorMessage' => 'The appointment was not found.',
                    'lastModified' => time()
                ], 404);
            }

            // Update the reserved process with new client data
            $reservedProcess['clients'][0]['familyName'] = $familyName;
            $reservedProcess['clients'][0]['email'] = $email;
            $reservedProcess['clients'][0]['telephone'] = $telephone;
            $reservedProcess['customTextfield'] = $customTextfield;

            // Submit the updated client data
            $updatedProcess = ZmsApiFacadeService::updateClientData($reservedProcess);

            // Handle errors if there are any
            if (isset($updatedProcess['error']) && $updatedProcess['error'] === 'tooManyAppointmentsWithSameMail') {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'tooManyAppointmentsWithSameMail',
                    'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.',
                    'lastModified' => time()
                ], 406);
            }

            // Return the updated process data
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