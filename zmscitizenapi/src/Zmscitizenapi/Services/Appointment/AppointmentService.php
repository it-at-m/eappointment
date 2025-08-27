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
        } elseif (!is_null($user)) {
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

        $thinnedScope = null;
        if ($process->scope instanceof Scope) {
            $thinnedScope = self::scopeToThinnedScope($process->scope);
        }

        $thinnedProcess->scope = $thinnedScope;
        return $thinnedProcess;
    }

    /**
     * Probably a duplicate of BO\Zmscitizenapi\Services\Core\MapperService::scopeToThinnedScope
     */
    public static function scopeToThinnedScope(Scope $scope): ThinnedScope
    {
        $providerList = ZmsApiClientService::getOffices() ?? new ProviderList();
        $providerMap = [];
        foreach ($providerList as $provider) {
            $key = $provider->getSource() . '_' . $provider->id;
            $providerMap[$key] = $provider;
        }

        $scopeProvider = $scope->getProvider();
        $providerKey = $scopeProvider ? ($scopeProvider->getSource() . '_' . $scopeProvider->id) : null;
        $matchingProvider = $providerKey && isset($providerMap[$providerKey]) ? $providerMap[$providerKey] : $scopeProvider;
        $thinnedProvider = MapperService::providerToThinnedProvider($matchingProvider);
        return new ThinnedScope(
            id: (int) $scope->id,
            provider: $thinnedProvider,
            shortName: (string) $scope->getShortName() ?? null,
            emailFrom: (string) $scope->getEmailFrom() ?? null,
            emailRequired: (bool) $scope->getEmailRequired() ?? false,
            telephoneActivated: (bool) $scope->getTelephoneActivated() ?? false,
            telephoneRequired: (bool) $scope->getTelephoneRequired() ?? false,
            customTextfieldActivated: (bool) $scope->getCustomTextfieldActivated() ?? false,
            customTextfieldRequired: (bool) $scope->getCustomTextfieldRequired() ?? false,
            customTextfieldLabel: $scope->getCustomTextfieldLabel() ?? null,
            customTextfield2Activated: (bool) $scope->getCustomTextfield2Activated() ?? false,
            customTextfield2Required: (bool) $scope->getCustomTextfield2Required() ?? false,
            customTextfield2Label: $scope->getCustomTextfield2Label() ?? null,
            captchaActivatedRequired: (bool) $scope->getCaptchaActivatedRequired() ?? false,
            displayInfo: $scope->getDisplayInfo() ?? null,
            slotsPerAppointment: ((string) $scope->getSlotsPerAppointment() === '' ? null : (string) $scope->getSlotsPerAppointment()) ?? null,
            appointmentsPerMail: ((string) $scope->getAppointmentsPerMail() === '' ? null : (string) $scope->getAppointmentsPerMail()) ?? null,
            whitelistedMails: ((string) $scope->getWhitelistedMails() === '' ? null : (string) $scope->getWhitelistedMails()) ?? null
        );
    }
}
