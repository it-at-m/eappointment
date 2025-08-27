<?php

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Exceptions\UnauthorizedException;
use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\MapperService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiClientService;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Scope;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

abstract class AppointmentService
{

    public static function getThinnedProcessById(int $processId, ?string $authKey, ?AuthenticatedUser $user): ThinnedProcess|array
    {
        // AuthKey check needs to be first
        if (!is_null($authKey)) {
            $process = ZmsApiClientService::getProcessById($processId, $authKey);
        } else if (!is_null($user)) {
            $externalUserId = $user->getExternalUserId();
            $process = ZmsApiClientService::getProcessByIdAuthenticated($processId);
            if ($externalUserId !== $process->getExternalUserId()) {
                throw new UnauthorizedException();
            }
        } else {
            throw new RuntimeException("Neither authenticated user or auth-key provided");
        }
        $errors = ValidationService::validateGetProcessNotFound($process);
        if (is_array($errors) && !empty($errors['errors'])) {
            return $errors;
        }
        $thinnedProcess = MapperService::processToThinnedProcess($process);

        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            $key = $provider->getSource() . '_' . $provider->id;
            $providerMap[$key] = $provider;
        }

        $thinnedScope = null;
        if ($process->scope instanceof Scope) {
            $scopeProvider = $process->scope->getProvider();
            $providerKey = $scopeProvider ? ($scopeProvider->getSource() . '_' . $scopeProvider->id) : null;
            $matchingProvider = $providerKey && isset($providerMap[$providerKey]) ? $providerMap[$providerKey] : $scopeProvider;
            $thinnedProvider = MapperService::providerToThinnedProvider($matchingProvider);
            $thinnedScope = new ThinnedScope(
                id: (int) $process->scope->id,
                provider: $thinnedProvider,
                shortName: (string) $process->scope->getShortName() ?? null,
                emailFrom: (string) $process->scope->getEmailFrom() ?? null,
                emailRequired: (bool) $process->scope->getEmailRequired() ?? false,
                telephoneActivated: (bool) $process->scope->getTelephoneActivated() ?? false,
                telephoneRequired: (bool) $process->scope->getTelephoneRequired() ?? false,
                customTextfieldActivated: (bool) $process->scope->getCustomTextfieldActivated() ?? false,
                customTextfieldRequired: (bool) $process->scope->getCustomTextfieldRequired() ?? false,
                customTextfieldLabel: $process->scope->getCustomTextfieldLabel() ?? null,
                customTextfield2Activated: (bool) $process->scope->getCustomTextfield2Activated() ?? false,
                customTextfield2Required: (bool) $process->scope->getCustomTextfield2Required() ?? false,
                customTextfield2Label: $process->scope->getCustomTextfield2Label() ?? null,
                captchaActivatedRequired: (bool) $process->scope->getCaptchaActivatedRequired() ?? false,
                displayInfo: $process->scope->getDisplayInfo() ?? null,
                slotsPerAppointment: ((string) $process->scope->getSlotsPerAppointment() === '' ? null : (string) $process->scope->getSlotsPerAppointment()) ?? null,
                appointmentsPerMail: ((string) $process->scope->getAppointmentsPerMail() === '' ? null : (string) $process->scope->getAppointmentsPerMail()) ?? null,
                whitelistedMails: ((string) $process->scope->getWhitelistedMails() === '' ? null : (string) $process->scope->getWhitelistedMails()) ?? null
            );
        }

        $thinnedProcess->scope = $thinnedScope;
        return $thinnedProcess;
    }

}
