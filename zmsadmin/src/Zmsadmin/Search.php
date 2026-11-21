<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Log as LogEntity;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Exception\UserAccountAccessRightsFailed;

class Search extends BaseController
{
    private const int DEFAULT_RESULTS_PER_PAGE = 100;

    private const int MAX_RESULTS_PER_PAGE = 1000;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        $parameters = $this->readSearchParameters($request->getAttribute('validator'));
        if ($workstation->getUseraccount()->hasRole('audit_viewer')) {
            $parameters['hideNavigation'] = 1;
        }
        $scopeList = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList();
        $scopeIds = $scopeList->getIds();

        if (array_diff($parameters['selectedScopeIds'], $scopeIds) !== []) {
            throw new UserAccountAccessRightsFailed();
        }

        [$processList, $processSearchTotal] = $this->readProcessSearchResults(
            $workstation,
            $parameters,
            $scopeIds
        );
        [$logList, $logSearchTotal] = $this->readLogSearchResults($workstation, $parameters, $scopeIds);

        return Render::withHtml(
            $response,
            'page/search.twig',
            array(
                'title' => 'Suche',
                'hideNavigation' => (bool) $parameters['hideNavigation'],
                'service' => $parameters['service'],
                'selectedScopeIds' => $parameters['selectedScopeIds'],
                'scopeList' => $scopeList->sortByContactName(),
                'userAction' => $parameters['userAction'],
                'date' => $parameters['date'],
                'page' => $parameters['page'],
                'perPage' => $parameters['perPage'],
                'workstation' => $workstation,
                'processList' => $processList,
                'logList' => $logList ?? [],
                'searchProcessQuery' => $parameters['queryString'],
                'processSearchTotal' => $processSearchTotal,
                'logSearchTotal' => $logSearchTotal,
                'menuActive' => 'search'
            )
        );
    }

    private function readSearchParameters(\BO\Mellon\Validator $validator): array
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
        $selectedScopeIds = $this->readSelectedScopeIds($validator);
        $date = $validator->getParameter('date')->isString()->setDefault(null)->getValue();
        $page = $this->readNumberParameter($validator, 'page', 1);
        $userAction = $this->readNumberParameter($validator, 'user', 0);
        $requestedResultsPerPage = $this->readNumberParameter($validator, 'perPage', self::DEFAULT_RESULTS_PER_PAGE);
        $resultsPerPage = max(1, min($requestedResultsPerPage, self::MAX_RESULTS_PER_PAGE));
        $hideNavigation = $this->readNumberParameter($validator, 'hideNavigation', 0);

        return [
            'queryString' => $queryString,
            'page' => $page,
            'service' => $service ? trim($service) : null,
            'selectedScopeIds' => $selectedScopeIds,
            'date' => $date !== null && trim($date) !== '' ? trim($date) : null,
            'userAction' => $userAction,
            'perPage' => $resultsPerPage,
            'hideNavigation' => $hideNavigation,
            'isSearchRequested' => (
                trim($queryString) !== ''
                || trim($service) !== ''
                || $selectedScopeIds !== []
                || ($date !== null && trim($date) !== '')
                || $userAction !== 0
            ),
        ];
    }

    private function readProcessSearchResults(\BO\Zmsentities\Workstation $workstation, array $parameters, array $scopeIds): array
    {
        if (!$this->shouldRunProcessSearch($workstation, $parameters)) {
            return [new ProcessList(), 0];
        }

        $searchParameters = $this->buildProcessSearchParameters($workstation, $parameters, $scopeIds);
        $searchResult = \App::$http->readGetResult('/process/search/', $searchParameters);
        $processList = $searchResult->getCollection() ?? new ProcessList();
        $searchMeta = $searchResult->getMeta();
        $processSearchTotal = isset($searchMeta->totalCount)
            ? (int) $searchMeta->totalCount
            : $processList->count();

        if (!empty($processList) && !$workstation->getUseraccount()->isSuperUser()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        return [$processList, $processSearchTotal];
    }

    private function shouldRunProcessSearch(\BO\Zmsentities\Workstation $workstation, array $parameters): bool
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
            || $parameters['selectedScopeIds'] !== []
            || $parameters['date'] !== null;
    }

    private function shouldRunLogSearch(\BO\Zmsentities\Workstation $workstation, array $parameters): bool
    {
        if (!$workstation->getUseraccount()->hasPermissions(['logs'])) {
            return false;
        }

        if ($this->hasLogSearchFilters($parameters)) {
            return true;
        }

        return $workstation->getUseraccount()->isSuperUser()
            && !$parameters['isSearchRequested'];
    }

    private function hasLogSearchFilters(array $parameters): bool
    {
        return trim((string) $parameters['queryString']) !== ''
            || $parameters['service'] !== null
            || $parameters['selectedScopeIds'] !== []
            || $parameters['date'] !== null
            || $parameters['userAction'] !== 0;
    }

    private function buildProcessSearchParameters(\BO\Zmsentities\Workstation $workstation, array $parameters, array $scopeIds): array
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
        if ($parameters['selectedScopeIds'] !== []) {
            $searchParameters['selectedScopeIds'] = $parameters['selectedScopeIds'];
        }
        if ($parameters['date'] !== null) {
            $searchParameters['date'] = $parameters['date'];
        }
        if (!$workstation->getUseraccount()->isSuperUser()) {
            $searchParameters['scopeIds'] = implode(',', $scopeIds);
        }

        return $searchParameters;
    }

    private function readLogSearchResults(\BO\Zmsentities\Workstation $workstation, array $parameters, array $scopeIds): array
    {
        if (!$this->shouldRunLogSearch($workstation, $parameters)) {
            return [null, 0];
        }

        $logParameters = [
            'searchQuery' => urlencode((string) $parameters['queryString']),
            'page' => $parameters['page'],
            'perPage' => $parameters['perPage'],
            'service' => $parameters['service'],
            'userAction' => $parameters['userAction'],
            'date' => $parameters['date'],
        ];
        if ($parameters['selectedScopeIds'] !== []) {
            $logParameters['scopeIds'] = implode(',', $parameters['selectedScopeIds']);
        } elseif (!$workstation->getUseraccount()->isSuperUser()) {
            $logParameters['scopeIds'] = implode(',', $scopeIds);
        }

        $logResult = \App::$http->readGetResult("/log/process/", $logParameters);
        $logList = $logResult->getCollection();
        $logMeta = $logResult->getMeta();

        $filteredLogList = $this->filterLogListForUserRights(
            $logList,
            $scopeIds,
            $workstation->getUseraccount()->isSuperUser()
        );
        $logSearchTotal = isset($logMeta->totalCount)
            ? (int) $logMeta->totalCount
            : $filteredLogList->count();

        return [$filteredLogList, $logSearchTotal];
    }

    private function filterProcessListForUserRights(?ProcessList $processList, array $scopeIds): ProcessList
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
    ): LogList {
        if (!isset($logList) || !$logList) {
            $logList = new LogList();
        }

        $list = new LogList();

        foreach ($logList as $log) {
            $log->display = LogEntity::formatDisplayFields($log->getArrayCopy());

            if (
                $bypassScopeFilter
                || (isset($log->scope_id) && in_array($log->scope_id, $scopeIds))
            ) {
                $list->addEntity(clone $log);
            }
        }

        return $list;
    }

    private function readSelectedScopeIds(\BO\Mellon\Validator $validator): array
    {
        $selection = $validator->getParameter('selectedScopeIds')->getUnvalidated() ?? [];
        if (!is_array($selection) || !array_is_list($selection)) {
            throw new \BO\Zmsadmin\Exception\BadRequest('Ungültige Kalender-Auswahl.');
        }

        $scopeIds = [];
        foreach ($selection as $value) {
            $scopeId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($scopeId === false) {
                throw new \BO\Zmsadmin\Exception\BadRequest('Ungültige Kalender-ID.');
            }
            $scopeIds[] = $scopeId;
        }

        return array_values(array_unique($scopeIds));
    }

    private function readStringParameter(\BO\Mellon\Validator $validator, string $name, string $default = ''): string
    {
        return $validator->getParameter($name)
            ->isString()
            ->setDefault($default)
            ->getValue() ?? $default;
    }

    private function readNumberParameter(\BO\Mellon\Validator $validator, string $name, int $default): int
    {
        return (int) $validator->getParameter($name)
            ->isNumber()
            ->setDefault($default)
            ->getValue();
    }
}
