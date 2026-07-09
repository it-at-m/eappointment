<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Collection\ProcessList;

class Search extends BaseController
{
    private const DEFAULT_RESULTS_PER_PAGE = 100;

    private const MAX_RESULTS_PER_PAGE = 1000;

    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $parameters = $this->readSearchParameters($request->getAttribute('validator'));
        if ($workstation->getUseraccount()->hasRole('audit_viewer')) {
            $parameters['hideNavigation'] = 1;
        }
        $scopeIds = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList()->getIds();

        [$processList, $processSearchTotal] = $this->readProcessSearchResults(
            $workstation,
            $parameters,
            $scopeIds
        );
        $logList = $this->readLogSearchResults($workstation, $parameters, $scopeIds);
        [$processList, $processListOther] = $this->splitProcessListsByScope($workstation, $processList);

        return Render::withHtml(
            $response,
            'page/search.twig',
            array(
                'title' => 'Suche',
                'hideNavigation' => (bool) $parameters['hideNavigation'],
                'service' => $parameters['service'],
                'provider' => $parameters['provider'],
                'userAction' => $parameters['userAction'],
                'date' => $parameters['date'],
                'page' => $parameters['page'],
                'perPage' => $parameters['perPage'],
                'workstation' => $workstation,
                'processList' => $processList,
                'processListOther' => $processListOther,
                'logList' => $logList ?? [],
                'searchProcessQuery' => $parameters['queryString'],
                'processSearchTotal' => $processSearchTotal,
                'menuActive' => 'search'
            )
        );
    }

    private function readSearchParameters($validator): array
    {
        $queryString = $validator->getParameter('query')
            ->isString('', false)
            ->getValue();
        if ($queryString !== null && $queryString !== '') {
            $queryString = html_entity_decode((string) $queryString, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            $queryString = $queryString ?? '';
        }

        $service = $this->readStringParameter($validator, 'service');
        $provider = $this->readStringParameter($validator, 'provider');
        $date = $validator->getParameter('date')->isString()->setDefault(null)->getValue();
        $page = $this->readNumberParameter($validator, 'page', 1);
        $userAction = $this->readNumberParameter($validator, 'user', 0);
        $requestedResultsPerPage = $this->readNumberParameter($validator, 'perPage', self::DEFAULT_RESULTS_PER_PAGE);
        $resultsPerPage = min($requestedResultsPerPage, self::MAX_RESULTS_PER_PAGE);
        $hideNavigation = $this->readNumberParameter($validator, 'hideNavigation', 0);

        return [
            'queryString' => $queryString,
            'page' => $page,
            'service' => $service ? trim($service) : null,
            'provider' => $provider ? trim($provider) : null,
            'date' => $date !== null && trim($date) !== '' ? trim($date) : null,
            'userAction' => $userAction,
            'perPage' => $resultsPerPage,
            'hideNavigation' => $hideNavigation,
            'isSearchRequested' => (
                trim((string) $queryString) !== ''
                || trim($service) !== ''
                || trim($provider) !== ''
                || ($date !== null && trim($date) !== '')
                || $userAction !== 0
            ),
        ];
    }

    private function readProcessSearchResults($workstation, array $parameters, array $scopeIds): array
    {
        if (!$this->shouldRunProcessSearch($workstation, $parameters)) {
            return [new ProcessList(), 0];
        }

        $searchParameters = $this->buildProcessSearchParameters($workstation, $parameters, $scopeIds);
        $searchResult = \App::$http->readGetResult('/process/search/', $searchParameters);
        $processList = $searchResult->getCollection();
        $searchMeta = $searchResult->getMeta();
        $processSearchTotal = isset($searchMeta->totalCount)
            ? (int) $searchMeta->totalCount
            : $processList->count();

        if (!empty($processList) && !$workstation->getUseraccount()->isSuperUser()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        return [$processList, $processSearchTotal];
    }

    private function shouldRunProcessSearch($workstation, array $parameters): bool
    {
        if (!$parameters['isSearchRequested']) {
            return false;
        }

        if (!$workstation->getUseraccount()->hasPermissions(['customersearch'])) {
            return false;
        }

        $queryString = trim((string) $parameters['queryString']);

        return $queryString !== '' || $this->hasStructuredSearchFilters($parameters);
    }

    private function hasStructuredSearchFilters(array $parameters): bool
    {
        return $parameters['service'] !== null
            || $parameters['provider'] !== null
            || $parameters['date'] !== null;
    }

    private function buildProcessSearchParameters($workstation, array $parameters, array $scopeIds): array
    {
        $queryString = trim((string) $parameters['queryString']);
        $searchParameters = [
            'resolveReferences' => 1,
            'page' => $parameters['page'],
            'limit' => $parameters['perPage'],
        ];
        if ($queryString !== '') {
            $searchParameters['query'] = $queryString;
        }
        if ($parameters['service'] !== null) {
            $searchParameters['service'] = $parameters['service'];
        }
        if ($parameters['provider'] !== null) {
            $searchParameters['provider'] = $parameters['provider'];
        }
        if ($parameters['date'] !== null) {
            $searchParameters['date'] = $parameters['date'];
        }
        if (!$workstation->getUseraccount()->isSuperUser()) {
            $searchParameters['scopeIds'] = implode(',', $scopeIds);
        }

        return $searchParameters;
    }

    private function readLogSearchResults($workstation, array $parameters, array $scopeIds): ?LogList
    {
        if (!$workstation->getUseraccount()->hasPermissions(['logs'])) {
            return null;
        }

        if (!$parameters['isSearchRequested'] && !$workstation->getUseraccount()->isSuperUser()) {
            return null;
        }

        $logParameters = [
            'searchQuery' => urlencode((string) $parameters['queryString']),
            'page' => $parameters['page'],
            'perPage' => $parameters['perPage'],
            'service' => $parameters['service'],
            'provider' => $parameters['provider'],
            'userAction' => $parameters['userAction'],
            'date' => $parameters['date'],
        ];
        if (!$workstation->getUseraccount()->isSuperUser()) {
            $logParameters['scopeIds'] = implode(',', $scopeIds);
        }

        $logList = \App::$http
            ->readGetResult("/log/process/", $logParameters)
            ->getCollection();

        return $this->filterLogListForUserRights(
            $logList,
            $scopeIds,
            $workstation->getUseraccount()->isSuperUser()
        );
    }

    private function splitProcessListsByScope($workstation, ?ProcessList $processList): array
    {
        $processList = $processList ?? new ProcessList();
        $processListOther = new ProcessList();
        if (!$workstation->getUseraccount()->isSuperUser()) {
            $processListOther = $processList->withOutScopeId($workstation->scope['id']);
            $processList = $processList->withScopeId($workstation->scope['id']);
        }

        return [$processList, $processListOther];
    }

    private function filterProcessListForUserRights(?ProcessList $processList, array $scopeIds)
    {
        if (empty($processList)) {
            return new ProcessList();
        }

        $list = new ProcessList();

        foreach ($processList as $process) {
            if (in_array($process->scope->id, $scopeIds)) {
                $list->addEntity(clone $process);
            }
        }

        return $list;
    }

    private function filterLogListForUserRights(
        ?LogList $logList,
        array $scopeIds,
        bool $bypassScopeFilter = false
    ) {
        if (!isset($logList) || !$logList) {
            $logList = new LogList();
        }

        $list = new LogList();

        foreach ($logList as $log) {
            $data = isset($log->data) ? json_decode($log->data, true) : null;
            $log->data = $data;

            if (
                $bypassScopeFilter
                || (isset($log->scope_id) && in_array($log->scope_id, $scopeIds))
            ) {
                $list->addEntity(clone $log);
            }
        }

        return $list;
    }

    private function readStringParameter($validator, string $name, string $default = ''): string
    {
        return $validator->getParameter($name)
            ->isString()
            ->setDefault($default)
            ->getValue() ?? $default;
    }

    private function readNumberParameter($validator, string $name, int $default): int
    {
        return (int) $validator->getParameter($name)
            ->isNumber()
            ->setDefault($default)
            ->getValue();
    }
}
