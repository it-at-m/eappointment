<?php

namespace BO\Zmsbackend\Mail\Api;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsbackend\Helper\User;
use BO\Zmsbackend\Mail\Exception\MailTemplateCopyInvalidInput;
use BO\Zmsbackend\Mail\Service\MailTemplates;
use BO\Zmsbackend\Scope\Exception\ScopeNotFound;
use BO\Zmsbackend\Scope\Service\Scope;
use BO\Zmsentities\Useraccount\EntityAccess;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MailTemplatesCopy extends \BO\Zmsbackend\Api\BaseController
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
        $userAccess = new User($request, 2);
        $userAccess->checkPermissions('mailtemplates');

        $input = Validator::input()->isJson()->getValue();

        if (!is_array($input)) {
            throw new MailTemplateCopyInvalidInput();
        }

        $sourceScopeId = $this->readPositiveInt(
            $input['sourceScopeId'] ?? null
        );
        $sourceTemplateId = $this->readPositiveInt(
            $input['sourceTemplateId'] ?? null
        );
        $targetScopeIds = $this->readTargetScopeIds(
            $input['targetScopeIds'] ?? null
        );

        if (
            $sourceScopeId === null
            || $sourceTemplateId === null
            || $targetScopeIds === null
            || count($targetScopeIds) === 0
            || in_array($sourceScopeId, $targetScopeIds, true)
        ) {
            throw new MailTemplateCopyInvalidInput();
        }

        $scopeIds = array_values(
            array_unique(
                array_merge([$sourceScopeId], $targetScopeIds)
            )
        );

        $scopeReader = new Scope();

        $scopeList = $scopeReader->readEntitiesByIds(
            $scopeIds,
            1
        );

        if (count($scopeList) !== count($scopeIds)) {
            throw new ScopeNotFound();
        }

        /*
         * Neben der fachlichen Berechtigung wird geprüft, ob der Benutzer
         * auf jeden Quell- und Zielstandort zugreifen darf.
         */
        foreach ($scopeList as $scope) {
            $userAccess->checkPermissions(
                new EntityAccess($scope)
            );
        }

        $sourceScope = $scopeList[$sourceScopeId];
        $sourceProviderId = (string) $sourceScope->getProviderId();

        $targetProviderIds = [];

        foreach ($targetScopeIds as $targetScopeId) {
            $targetScope = $scopeList[$targetScopeId];
            $targetProviderId = (string) $targetScope->getProviderId();

            /*
             * Mail-Templates sind technisch einem Provider zugeordnet.
             * Ein Standort mit demselben Provider wie die Quelle darf daher
             * nicht als Ziel verwendet werden.
             */
            if ($targetProviderId === $sourceProviderId) {
                throw new MailTemplateCopyInvalidInput();
            }

            $targetProviderIds[] = $targetProviderId;
        }

        $targetProviderIds = array_values(
            array_unique($targetProviderIds)
        );

        if (count($targetProviderIds) === 0) {
            throw new MailTemplateCopyInvalidInput();
        }

        $this->assertTargetProviderAccess(
            $targetProviderIds,
            $scopeReader,
            $userAccess
        );

        $template = (new MailTemplates())
            ->copyCustomizationToProviders(
                $sourceTemplateId,
                $sourceProviderId,
                $targetProviderIds
            );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $template;

        $response = Render::withLastModified(
            $response,
            time(),
            '0'
        );

        return Render::withJson(
            $response,
            $message,
            $message->getStatuscode()
        );
    }

    /**
     * @param string[] $targetProviderIds
     */
    private function assertTargetProviderAccess(
        array $targetProviderIds,
        Scope $scopeReader,
        User $userAccess
    ): void {
        foreach ($targetProviderIds as $targetProviderId) {
            $providerScopes = $scopeReader->readByProviderId(
                $targetProviderId,
                0
            );

            if ($providerScopes->count() === 0) {
                throw new ScopeNotFound();
            }

            foreach ($providerScopes as $providerScope) {
                $userAccess->checkPermissions(
                    new EntityAccess($providerScope)
                );
            }
        }
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

    private function readTargetScopeIds(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $scopeIds = [];

        foreach ($value as $scopeId) {
            $validatedScopeId = $this->readPositiveInt($scopeId);

            if ($validatedScopeId === null) {
                return null;
            }

            $scopeIds[] = $validatedScopeId;
        }

        return array_values(array_unique($scopeIds));
    }
}
