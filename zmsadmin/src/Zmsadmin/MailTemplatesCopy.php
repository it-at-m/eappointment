<?php

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsclient\Exception;
use BO\Zmsentities\Collection\MailtemplateList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Workstation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailTemplatesCopy extends BaseController
{
    /**
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $workstation = $this->readAuthorizedWorkstation();
        $queryParameters = $this->readQueryParameters($request);
        $scopeList = $this->readScopeList($workstation);

        $input = (array) ($request->getParsedBody() ?? []);
        $isPostRequest = strtoupper($request->getMethod()) === 'POST';

        $sourceScopeId = $this->resolveSourceScopeId(
            $input,
            $queryParameters['sourceScopeId'],
            $isPostRequest,
            $workstation,
            $scopeList
        );

        [
            $selectedSourceScope,
            $formError,
        ] = $this->resolveSourceScope(
            $sourceScopeId,
            $isPostRequest,
            $scopeList
        );

        $customTemplates = $this->readCustomTemplates(
            $selectedSourceScope
        );

        $sourceTemplateId = null;
        $selectedTargetScopeIds = [];

        if ($isPostRequest) {
            $postResult = $this->processPostRequest(
                $input,
                $sourceScopeId,
                $selectedSourceScope,
                $scopeList,
                $customTemplates,
                $formError
            );

            if ($postResult['response'] instanceof ResponseInterface) {
                return $postResult['response'];
            }

            $sourceTemplateId =
                $postResult['sourceTemplateId'];

            $selectedTargetScopeIds =
                $postResult['selectedTargetScopeIds'];

            $formError = $postResult['formError'];
        }

        return Render::withHtml(
            $response,
            'page/mailtemplatesCopy.twig',
            [
                'title' => 'E-Mail-Template kopieren',
                'pageTitle' => 'E-Mail-Template kopieren',
                'menuActive' => 'mailtemplatesCopy',
                'workstation' => $workstation,
                'scopeList' => $scopeList,
                'sourceScopeId' => $sourceScopeId,
                'selectedSourceScope' => $selectedSourceScope,
                'customTemplates' => $customTemplates,
                'selectedSourceTemplateId' => $sourceTemplateId,
                'selectedTargetScopeIds' =>
                    $selectedTargetScopeIds,
                'formError' => $formError,
                'success' => $queryParameters['success'],
                'copiedCount' =>
                    $queryParameters['copiedCount'],
                'targetScopeOptions' =>
                    $this->buildTargetScopeOptions(
                        $workstation,
                        $scopeList,
                        $selectedSourceScope
                    ),
            ]
        );
    }

    private function readAuthorizedWorkstation(): Workstation
    {
        $workstation = \App::$http
            ->readGetResult(
                '/workstation/',
                ['resolveReferences' => 3]
            )
            ->getEntity();

        if (
            !$workstation
                ->getUseraccount()
                ->hasPermissions(['mailtemplates'])
        ) {
            throw new UserAccountMissingRights();
        }

        return $workstation;
    }

    private function readQueryParameters(
        RequestInterface $request
    ): array {
        $validator = $request->getAttribute('validator');

        return [
            'success' => $validator
                ->getParameter('success')
                ->isString()
                ->getValue(),
            'copiedCount' => (int) $validator
                ->getParameter('copiedCount')
                ->isNumber()
                ->setDefault(0)
                ->getValue(),
            'sourceScopeId' => $validator
                ->getParameter('sourceScopeId')
                ->isNumber()
                ->getValue(),
        ];
    }

    private function readScopeList(Workstation $workstation): ScopeList
    {
        /*
         * Dieselbe Menge wie backend EntityAccess/hasScope():
         * zugewiesene Departments inklusive Cluster-Standorte.
         */
        return $workstation
            ->getUseraccount()
            ->getDepartmentList()
            ->getUniqueScopeList()
            ->sortByContactName();
    }

    private function resolveSourceScopeId(
        array $input,
        mixed $requestedSourceScopeId,
        bool $isPostRequest,
        Workstation $workstation,
        ScopeList $scopeList
    ): ?int {
        $sourceScopeValue = $isPostRequest
            ? ($input['sourceScopeId'] ?? null)
            : $requestedSourceScopeId;

        $sourceScopeId = $this->readPositiveInt(
            $sourceScopeValue
        );

        if ($sourceScopeId !== null || $isPostRequest) {
            return $sourceScopeId;
        }

        /*
         * Beim ersten Seitenaufruf wird der aktuell ausgewählte
         * Arbeitsplatzstandort als Quelle vorausgewählt.
         */
        $currentScopeId = $this->readPositiveInt(
            $workstation->getScope()->getId()
        );

        if (
            $currentScopeId !== null
            && $scopeList->hasEntity($currentScopeId)
        ) {
            return $currentScopeId;
        }

        return null;
    }

    private function resolveSourceScope(
        ?int $sourceScopeId,
        bool $isPostRequest,
        ScopeList $scopeList
    ): array {
        if ($sourceScopeId === null) {
            $formError = $isPostRequest
                ? 'Bitte wählen Sie einen gültigen '
                    . 'Quellstandort aus.'
                : null;

            return [null, $formError];
        }

        $selectedSourceScope = $scopeList->getEntity(
            $sourceScopeId
        );

        if ($selectedSourceScope === null) {
            return [
                null,
                'Der ausgewählte Quellstandort wurde nicht '
                    . 'gefunden oder darf nicht verwendet werden.',
            ];
        }

        return [$selectedSourceScope, null];
    }

    private function readCustomTemplates(
        ?Scope $selectedSourceScope
    ): MailtemplateList {
        $customTemplates = new MailtemplateList();

        if ($selectedSourceScope === null) {
            return $customTemplates;
        }

        $providerId = (string)
            $selectedSourceScope->getProviderId();

        $loadedTemplates = \App::$http
            ->readGetResult(
                '/custom-mailtemplates/' . $providerId . '/'
            )
            ->getCollection();

        if ($loadedTemplates === null) {
            return $customTemplates;
        }

        $loadedTemplates->prioritizeByName([
            'mail_preconfirmed.twig',
            'mail_confirmation.twig',
            'mail_reminder.twig',
            'mail_delete.twig',
        ]);

        return $loadedTemplates;
    }

    private function processPostRequest(
        array $input,
        ?int $sourceScopeId,
        ?Scope $selectedSourceScope,
        ScopeList $scopeList,
        MailtemplateList $customTemplates,
        ?string $formError
    ): array {
        $sourceTemplateId = $this->readPositiveInt(
            $input['sourceTemplateId'] ?? null
        );

        $normalizedTargetScopeIds =
            $this->readTargetScopeIds(
                $input['targetScopeIds'] ?? null
            );

        $result = [
            'sourceTemplateId' => $sourceTemplateId,
            'selectedTargetScopeIds' =>
                $normalizedTargetScopeIds ?? [],
            'formError' => $formError,
            'response' => null,
        ];

        if ($result['formError'] !== null) {
            return $result;
        }

        if (
            $sourceScopeId === null
            || $selectedSourceScope === null
        ) {
            $result['formError'] =
                'Bitte wählen Sie einen gültigen '
                . 'Quellstandort aus.';

            return $result;
        }

        if (
            $sourceTemplateId === null
            || !$customTemplates->hasEntity(
                $sourceTemplateId
            )
        ) {
            $result['formError'] =
                'Bitte wählen Sie ein angepasstes '
                . 'E-Mail-Template des Quellstandorts aus.';

            return $result;
        }

        if (
            $normalizedTargetScopeIds === null
            || $normalizedTargetScopeIds === []
        ) {
            $result['formError'] =
                'Bitte wählen Sie mindestens einen '
                . 'Zielstandort aus.';

            return $result;
        }

        $targetScopeError = $this->validateTargetScopes(
            $normalizedTargetScopeIds,
            $selectedSourceScope,
            $scopeList
        );

        if ($targetScopeError !== null) {
            $result['formError'] = $targetScopeError;

            return $result;
        }

        $copyResult = $this->copyTemplate(
            $sourceScopeId,
            $sourceTemplateId,
            $normalizedTargetScopeIds,
            $scopeList
        );

        if ($copyResult instanceof ResponseInterface) {
            $result['response'] = $copyResult;
        } else {
            $result['formError'] = $copyResult;
        }

        return $result;
    }

    private function validateTargetScopes(
        array $targetScopeIds,
        Scope $selectedSourceScope,
        ScopeList $scopeList
    ): ?string {
        foreach ($targetScopeIds as $targetScopeId) {
            $targetScope = $scopeList->getEntity(
                $targetScopeId
            );

            if ($targetScope === null) {
                return
                    'Mindestens ein ausgewählter Zielstandort '
                    . 'wurde nicht gefunden oder darf nicht '
                    . 'verwendet werden.';
            }

            $isSourceScope =
                $targetScopeId
                === $selectedSourceScope->getId();

            if ($isSourceScope) {
                return
                    'Der Quellstandort darf nicht gleichzeitig '
                    . 'als Zielstandort ausgewählt werden.';
            }

            $hasSameProvider =
                (string) $targetScope->getProviderId()
                ===
                (string) $selectedSourceScope->getProviderId();

            if ($hasSameProvider) {
                return
                    'Der ausgewählte Zielstandort verwendet '
                    . 'denselben Provider wie der Quellstandort '
                    . 'und kann daher nicht als Zielstandort '
                    . 'verwendet werden.';
            }
        }

        return null;
    }

    private function copyTemplate(
        int $sourceScopeId,
        int $sourceTemplateId,
        array $targetScopeIds,
        ScopeList $scopeList
    ): ResponseInterface|string {
        try {
            $copiedTemplate = \App::$http
                ->readPostResult(
                    '/mailtemplates/copy/',
                    [
                        'sourceScopeId' => $sourceScopeId,
                        'sourceTemplateId' =>
                            $sourceTemplateId,
                        'targetScopeIds' => $targetScopeIds,
                    ]
                )
                ->getEntity();
        } catch (Exception $exception) {
            $backendMessage = trim(
                (string) $exception->originalMessage
            );

            if ($backendMessage !== '') {
                return $backendMessage;
            }

            return
                'Beim Kopieren des E-Mail-Templates ist ein '
                . 'Fehler aufgetreten.';
        }

        if ($copiedTemplate === null) {
            return
                'Das E-Mail-Template konnte nicht kopiert '
                . 'werden.';
        }

        return Render::redirect(
            'mailtemplatesCopy',
            [],
            [
                'sourceScopeId' => $sourceScopeId,
                'success' => 'mailtemplates_copied',
                'copiedCount' => $this->countUniqueProviders(
                    $targetScopeIds,
                    $scopeList
                ),
            ]
        );
    }

    private function readPositiveInt(mixed $value): ?int
    {
        if (
            (!is_int($value) && !is_string($value))
            || is_bool($value)
        ) {
            return null;
        }

        $validatedValue = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if (
            $validatedValue === false
            || $validatedValue < 1
        ) {
            return null;
        }

        return (int) $validatedValue;
    }

    private function readTargetScopeIds(
        mixed $value
    ): ?array {
        if (!is_array($value)) {
            return null;
        }

        $scopeIds = [];

        foreach ($value as $scopeId) {
            $validatedScopeId = $this->readPositiveInt(
                $scopeId
            );

            if ($validatedScopeId === null) {
                return null;
            }

            $scopeIds[] = $validatedScopeId;
        }

        return array_values(array_unique($scopeIds));
    }

    private function countUniqueProviders(
        array $targetScopeIds,
        ScopeList $scopeList
    ): int {
        $providerIds = [];

        foreach ($targetScopeIds as $targetScopeId) {
            $targetScope = $scopeList->getEntity(
                $targetScopeId
            );

            if ($targetScope === null) {
                continue;
            }

            $providerId = (string)
                $targetScope->getProviderId();

            if ($providerId !== '') {
                $providerIds[] = $providerId;
            }
        }

        return count(array_unique($providerIds));
    }

    /**
     * @return array<int, array{name: string, options: array<int, array{value: int, name: string}>}>
     */
    private function buildTargetScopeOptions(
        Workstation $workstation,
        ScopeList $scopeList,
        ?Scope $selectedSourceScope
    ): array {
        if ($selectedSourceScope === null) {
            return [];
        }

        $departments = $workstation
            ->getUseraccount()
            ->getDepartmentList()
            ->withMatchingScopes($scopeList);

        $seenProviderIds = [];
        $targetScopeOptions = [];

        foreach ($departments as $department) {
            $departmentScopeOptions = [];

            foreach ($department->scopes as $scope) {
                $providerId = (string) $scope->getProviderId();

                if (
                    !$this->isEligibleCopyTarget(
                        $scope,
                        $selectedSourceScope
                    )
                    || isset($seenProviderIds[$providerId])
                ) {
                    continue;
                }

                $seenProviderIds[$providerId] = true;
                $departmentScopeOptions[] = [
                    'value' => (int) $scope->getId(),
                    'name' => $this->formatScopeName($scope),
                ];
            }

            if ($departmentScopeOptions === []) {
                continue;
            }

            usort(
                $departmentScopeOptions,
                static function (array $left, array $right): int {
                    return strnatcasecmp(
                        (string) $left['name'],
                        (string) $right['name']
                    );
                }
            );

            $targetScopeOptions[] = [
                'name' => (string) $department->name,
                'options' => $departmentScopeOptions,
            ];
        }

        return $targetScopeOptions;
    }

    private function isEligibleCopyTarget(
        Scope $scope,
        Scope $selectedSourceScope
    ): bool {
        if (
            (int) $scope->getId()
            === (int) $selectedSourceScope->getId()
        ) {
            return false;
        }

        return (string) $scope->getProviderId()
            !== (string) $selectedSourceScope->getProviderId();
    }

    private function formatScopeName(Scope $scope): string
    {
        $contactName = trim((string) $scope->getName());
        $shortName = trim((string) $scope->getShortName());

        if ($contactName !== '') {
            return trim($contactName . ' ' . $shortName);
        }

        return sprintf(
            '(Standort gelöscht, ehemals: %s-%s)',
            (string) ($scope->provider['source'] ?? ''),
            (string) $scope->getProviderId()
        );
    }
}
