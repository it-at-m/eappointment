<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Collection\ProcessList;

class ProcessSearch extends BaseController
{
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
        $parameters = $this->getSearchParameters($request->getAttribute('validator'));
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

    private function getSearchParameters($validator): array
    {
        $queryString = $validator->getParameter('query')
            ->isString('', false)
            ->getValue();
        if ($queryString !== null && $queryString !== '') {
            $queryString = html_entity_decode((string) $queryString, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $service = $validator->getParameter('service')
            ->isString()
            ->setDefault('')
            ->getValue();
        $provider = $validator->getParameter('provider')
            ->isString()
            ->setDefault('')
            ->getValue();
        $perPage = $validator->getParameter('perPage')
            ->isNumber()
            ->setDefault(100)
            ->getValue();
        if ($perPage > 1000) {
            $perPage = 1000;
        }

        return [
            'queryString' => $queryString,
            'page' => (int) $validator->getParameter('page')->isNumber()->setDefault(1)->getValue(),
            'service' => $service ? trim($service) : null,
            'provider' => $provider ? trim($provider) : null,
            'date' => $validator->getParameter('date')->isString()->setDefault(null)->getValue(),
            'userAction' => (int) $validator->getParameter('user')->isNumber()->setDefault(0)->getValue(),
            'perPage' => $perPage,
        ];
    }

    private function readProcessSearchResults($workstation, array $parameters, array $scopeIds): array
    {
        $queryString = $parameters['queryString'];
        if ($queryString === null || $queryString === '') {
            return [new ProcessList(), 0];
        }

        $searchParameters = [
            'query' => $queryString,
            'resolveReferences' => 1,
            'page' => $parameters['page'],
            'limit' => $parameters['perPage'],
        ];
        if (!$workstation->hasSuperUseraccount()) {
            $searchParameters['scopeIds'] = implode(',', $scopeIds);
        }

        $searchResult = \App::$http->readGetResult('/process/search/', $searchParameters);
        $processList = $searchResult->getCollection();
        $searchMeta = $searchResult->getMeta();
        $processSearchTotal = isset($searchMeta->totalCount)
            ? (int) $searchMeta->totalCount
            : $processList->count();

        if (!empty($processList) && !$workstation->hasSuperUseraccount()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        return [$processList, $processSearchTotal];
    }

    private function readLogSearchResults($workstation, array $parameters, array $scopeIds): ?LogList
    {
        if (!$workstation->hasAuditAccount()) {
            return null;
        }

        $logList = \App::$http
            ->readGetResult("/log/process/", [
                'searchQuery' => urlencode((string) $parameters['queryString']),
                'page' => $parameters['page'],
                'perPage' => $parameters['perPage'],
                'service' => $parameters['service'],
                'provider' => $parameters['provider'],
                'userAction' => $parameters['userAction'],
                'date' => $parameters['date'],
            ])
            ->getCollection();

        return $this->filterLogListForUserRights($logList, $scopeIds);
    }

    private function splitProcessListsByScope($workstation, ?ProcessList $processList): array
    {
        $processList = $processList ?? new ProcessList();
        $processListOther = new ProcessList();
        if (!$workstation->hasSuperUseraccount()) {
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

    private function filterLogListForUserRights(?LogList $logList, array $scopeIds)
    {
        if (!isset($logList) || !$logList) {
            $logList = new LogList();
        }

        $list = new LogList();

        foreach ($logList as $log) {
            $data = isset($log->data) ? json_decode($log->data, true) : null;
            $log->data = $data;

            if (isset($log->scope_id) && in_array($log->scope_id, $scopeIds)) {
                $list->addEntity(clone $log);
            }
        }

        return $list;
    }
}
