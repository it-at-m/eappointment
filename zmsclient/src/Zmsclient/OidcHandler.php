<?php

namespace BO\Zmsclient;

use BO\Zmsclient\Auth;
use BO\Zmsclient\Http;
use BO\Zmsentities\Schema\Entity;

/**
 * Shared OIDC callback handler used by zmsadmin and zmsstatistic.
 *
 * Validates the state parameter against the session auth key with a
 * constant-time comparison and resolves the workstation/department state
 * needed by the application controller to decide on the next redirect.
 */
class OidcHandler
{
    private Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * Handle OIDC callback with secure state validation.
     *
     * @param string|null $state       State parameter from the OIDC callback
     * @param string      $application Application name for logging (e.g. zmsadmin)
     *
     * @throws \BO\Slim\Exception\OAuthInvalid when the state does not match
     * @throws \Throwable                      for downstream workstation errors
     *
     * @return array{
     *     workstation: mixed,
     *     department_count: int,
     *     redirect_to_index: bool
     * }
     */
    public function handleCallback(?string $state, string $application): array
    {
        $authKey = Auth::getKey();
        $sessionHash = hash('sha256', (string) $authKey);

        $stateIsValid = is_string($state)
            && is_string($authKey)
            && $state !== ''
            && $authKey !== ''
            && hash_equals($authKey, $state);

        \App::$log->info('OIDC Login state validation', [
            'event' => 'oauth_login_state_validation',
            'timestamp' => date('c'),
            'provider' => Auth::getOidcProvider(),
            'application' => $application,
            'state_match' => $stateIsValid,
            'hashed_session_token' => $sessionHash,
        ]);

        if (!$stateIsValid) {
            \App::$log->error('OIDC Login invalid state', [
                'event' => 'oauth_login_invalid_state',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => $application,
            ]);
            throw new \BO\Slim\Exception\OAuthInvalid();
        }

        return $this->authenticateWorkstation($application, $sessionHash);
    }

    /**
     * @return array{workstation: mixed, department_count: int, redirect_to_index: bool}
     */
    private function authenticateWorkstation(string $application, string $sessionHash): array
    {
        try {
            $workstation = $this->http
                ->readGetResult('/workstation/', ['resolveReferences' => 2])
                ->getEntity();

            if (!$workstation instanceof Entity) {
                throw new \RuntimeException('OIDC workstation lookup returned no entity');
            }

            $username = $workstation->getUseraccount()->id;
            $workstationAuthKey = $workstation['authkey'] ?? Auth::getKey() ?? '';
            $workstationHash = hash('sha256', (string) $workstationAuthKey);

            \App::$log->info('OIDC Login workstation access', [
                'event' => 'oauth_login_workstation_access',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => $application,
                'username' => $username,
                'workstation_id' => $workstation->id ?? 'unknown',
                'hashed_workstation_key' => $workstationHash,
            ]);

            $departmentCount = $workstation->getUseraccount()->getDepartmentList()->count();

            \App::$log->info('OIDC Login department check', [
                'event' => 'oauth_login_department_check',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => $application,
                'username' => $username,
                'department_count' => $departmentCount,
                'has_departments' => ($departmentCount > 0),
                'hashed_session_token' => $sessionHash,
            ]);

            return [
                'workstation' => $workstation,
                'department_count' => $departmentCount,
                'redirect_to_index' => (0 === $departmentCount),
            ];
        } catch (\Throwable $e) {
            \App::$log->error('OIDC Login workstation error', [
                'event' => 'oauth_login_workstation_error',
                'timestamp' => date('c'),
                'provider' => Auth::getOidcProvider(),
                'application' => $application,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }
}
