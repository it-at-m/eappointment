<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsclient\Exception;
use BO\Zmsentities\Collection\MailtemplateList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Exception\UserAccountMissingRights;
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
        $workstation = \App::$http
            ->readGetResult(
                '/workstation/',
                ['resolveReferences' => 1]
            )
            ->getEntity();

        if (
            !$workstation
                ->getUseraccount()
                ->hasPermissions(['mailtemplates'])
        ) {
            throw new UserAccountMissingRights();
        }

        $validator = $request->getAttribute('validator');

        $success = $validator
            ->getParameter('success')
            ->isString()
            ->getValue();

        $copiedCount = $validator
            ->getParameter('copiedCount')
            ->isNumber()
            ->setDefault(0)
            ->getValue();

        $requestedSourceScopeId = $validator
            ->getParameter('sourceScopeId')
            ->isNumber()
            ->getValue();

        /*
         * Die Owner-Liste wird mit allen untergeordneten Referenzen geladen.
         * Das Backend filtert die Liste bereits anhand der Zugriffsrechte.
         */
        $ownerList = \App::$http
            ->readGetResult(
                '/owner/',
                ['resolveReferences' => 4]
            )
            ->getCollection();

        $scopeList = $this->buildScopeList($ownerList);

        $input = (array) ($request->getParsedBody() ?? []);
        $isPostRequest = strtoupper($request->getMethod()) === 'POST';

        if ($isPostRequest) {
            $sourceScopeId = $this->readPositiveInt(
                $input['sourceScopeId'] ?? null
            );
        } else {
            $sourceScopeId = $this->readPositiveInt(
                $requestedSourceScopeId
            );
        }

        /*
         * Wird die Seite ohne sourceScopeId aufgerufen, wird der aktuell
         * ausgewählte Arbeitsplatzstandort als Quelle vorausgewählt.
         */
        if ($sourceScopeId === null && !$isPostRequest) {
            $currentScopeId = $this->readPositiveInt(
                $workstation->getScope()->getId()
            );

            if (
                $currentScopeId !== null
                && $scopeList->hasEntity($currentScopeId)
            ) {
                $sourceScopeId = $currentScopeId;
            }
        }

        $formError = null;
        $selectedSourceScope = null;
        $sourceTemplateId = null;
        $selectedTargetScopeIds = [];
        $customTemplates = new MailtemplateList();

        if ($sourceScopeId !== null) {
            $selectedSourceScope = $scopeList->getEntity(
                $sourceScopeId
            );

            if ($selectedSourceScope === null) {
                $formError =
                    'Der ausgewählte Quellstandort wurde nicht gefunden '
                    . 'oder darf nicht verwendet werden.';
            }
        } elseif ($isPostRequest) {
            $formError =
                'Bitte wählen Sie einen gültigen Quellstandort aus.';
        }

        /*
         * Es werden bewusst nur angepasste Templates geladen.
         * Standard-Templates dürfen nicht als Standortanpassung kopiert werden.
         */
        if ($selectedSourceScope !== null) {
            $providerId = (string) $selectedSourceScope->getProviderId();

            $loadedTemplates = \App::$http
                ->readGetResult(
                    '/custom-mailtemplates/' . $providerId . '/'
                )
                ->getCollection();

            if ($loadedTemplates !== null) {
                $customTemplates = $loadedTemplates;

                $customTemplates->prioritizeByName([
                    'mail_preconfirmed.twig',
                    'mail_confirmation.twig',
                    'mail_reminder.twig',
                    'mail_delete.twig',
                ]);
            }
        }

        if ($isPostRequest) {
            $sourceTemplateId = $this->readPositiveInt(
                $input['sourceTemplateId'] ?? null
            );

            $normalizedTargetScopeIds = $this->readTargetScopeIds(
                $input['targetScopeIds'] ?? null
            );

            if ($normalizedTargetScopeIds !== null) {
                $selectedTargetScopeIds = $normalizedTargetScopeIds;
            }

            if (
                $formError === null
                && (
                    $sourceTemplateId === null
                    || !$customTemplates->hasEntity($sourceTemplateId)
                )
            ) {
                $formError =
                    'Bitte wählen Sie ein angepasstes E-Mail-Template '
                    . 'des Quellstandorts aus.';
            }

            if (
                $formError === null
                && (
                    $normalizedTargetScopeIds === null
                    || count($selectedTargetScopeIds) === 0
                )
            ) {
                $formError =
                    'Bitte wählen Sie mindestens einen Zielstandort aus.';
            }

            if ($formError === null) {
                foreach ($selectedTargetScopeIds as $targetScopeId) {
                    $targetScope = $scopeList->getEntity($targetScopeId);

                    if ($targetScope === null) {
                        $formError =
                            'Mindestens ein ausgewählter Zielstandort '
                            . 'wurde nicht gefunden oder darf nicht '
                            . 'verwendet werden.';
                        break;
                    }

                    if (
                        $selectedSourceScope === null
                        || $targetScopeId === $selectedSourceScope->getId()
                        || (
                            (string) $targetScope->getProviderId()
                            ===
                            (string) $selectedSourceScope->getProviderId()
                        )
                    ) {
                        $formError =
                            'Der Quellstandort darf nicht gleichzeitig '
                            . 'als Zielstandort ausgewählt werden.';
                        break;
                    }
                }
            }

            if ($formError === null) {
                try {
                    /*
                     * Die gesamte Batch-Verarbeitung erfolgt mit genau
                     * einem Backend-Aufruf.
                     */
                    $copiedTemplate = \App::$http
                        ->readPostResult(
                            '/mailtemplates/copy/',
                            [
                                'sourceScopeId' => $sourceScopeId,
                                'sourceTemplateId' => $sourceTemplateId,
                                'targetScopeIds' => $selectedTargetScopeIds,
                            ]
                        )
                        ->getEntity();

                    if ($copiedTemplate === null) {
                        $formError =
                            'Das E-Mail-Template konnte nicht kopiert werden.';
                    } else {
                        return Render::redirect(
                            'mailtemplatesCopy',
                            [],
                            [
                                'sourceScopeId' => $sourceScopeId,
                                'success' => 'mailtemplates_copied',
                                'copiedCount' => count(
                                    $selectedTargetScopeIds
                                ),
                            ]
                        );
                    }
                } catch (Exception $exception) {
                    $formError =
                        'Beim Kopieren des E-Mail-Templates ist ein Fehler '
                        . 'aufgetreten. Es wurden keine Änderungen '
                        . 'übernommen.';
                }
            }
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
                'selectedTargetScopeIds' => $selectedTargetScopeIds,
                'formError' => $formError,
                'success' => $success,
                'copiedCount' => (int) $copiedCount,
            ]
        );
    }

    private function buildScopeList(iterable $ownerList): ScopeList
    {
        $scopeList = new ScopeList();

        foreach ($ownerList as $owner) {
            foreach ($owner->getOrganisationList() as $organisation) {
                foreach (
                    $organisation->getDepartmentList() as $department
                ) {
                    $scopeList->addScopeList(
                        $department->getScopeList()
                    );
                }
            }
        }

        return $scopeList
            ->withUniqueScopes()
            ->sortByContactName();
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

    /**
     * @return int[]|null
     */
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
