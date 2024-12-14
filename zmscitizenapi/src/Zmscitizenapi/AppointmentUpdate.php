<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\BaseController;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use \BO\Zmscitizenapi\Services\ValidationService;
use \BO\Zmscitizenapi\Helper\UtilityHelper;
use BO\Zmscitizenapi\Services\ExceptionService;
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

            error_log(json_encode($reservedProcess['data']['processId']));
            error_log(json_encode($reservedProcess['data']['email']));
            error_log(json_encode($reservedProcess['data']['familyName']));


            $reservedProcess['data']['familyName'] = $familyName;
            $reservedProcess['data']['email'] = $email;
            $reservedProcess['data']['telephone'] = $telephone;
            $reservedProcess['data']['customTextfield'] = $customTextfield;
            //$reservedProcess['data']['id'] = $processId;

            //error_log(json_encode($reservedProcess));

            $updatedProcess = ZmsApiFacadeService::updateClientData(Process::create($reservedProcess['data']));

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