<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Collection\ProcessList;

/**
  * Handle requests concerning services
  *
  */
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
        $validator = $request->getAttribute('validator');
        $searchParameters = $this->readSearchParameters($validator);

        $queryString = $searchParameters['queryString'];
        $page = $searchParameters['page'];
        $service = $searchParameters['service'];
        $provider = $searchParameters['provider'];
        $date = $searchParameters['date'];
        $userAction = $searchParameters['userAction'];
        $perPage = $searchParameters['perPage'];
        $hideNavigation = $searchParameters['hideNavigation'];
        $isSearchRequested = $searchParameters['isSearchRequested'];

        $processList = $this->readProcessList($isSearchRequested, $queryString);

        $scopeIds = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList()->getIds();
        if (!empty($processList) && !$workstation->hasSuperUseraccount()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        if ($workstation->hasAuditAccount() && $isSearchRequested) {
            $queryString = urlencode($queryString);
            $logList = \App::$http
                ->readGetResult("/log/process/", [
                        'searchQuery' => $queryString,
                        'page' => $page,
                        'perPage' => $perPage,
                        'service' => $service ? trim($service) : null,
                        'provider' => $provider ? trim($provider) : null,
                        'userAction' => (int) $userAction,
                        'date' => (trim($date) !== '' ? trim($date) : null)
                    ])
                ->getCollection();
            $logList = $this->filterLogListForUserRights($logList, $scopeIds);
        }

        $processList = $processList ?? new ProcessList();
        $processListOther = new ProcessList();
        if (!$workstation->hasSuperUseraccount()) {
            $processListOther = $processList->withOutScopeId($workstation->scope['id']);
            $processList = $processList->withScopeId($workstation->scope['id']);
        }
        return Render::withHtml(
            $response,
            'page/search.twig',
            array(
                'title' => 'Suche',
                'hideNavigation' => (bool) $hideNavigation,
                'service' => $service ? trim($service) : null,
                'provider' => $provider ? trim($provider) : null,
                'userAction' => (int) $userAction,
                'date' => $date,
                'page' => $page,
                'perPage' => $perPage,
                'workstation' => $workstation,
                'processList' => $processList,
                'processListOther' => $processListOther,
                'logList' => $logList ?? [],
                'searchProcessQuery' => urldecode((string) $queryString),
                'menuActive' => 'search'
            )
        );
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

    private function readSearchParameters($validator): array
    {
        $queryString = $this->readStringParameter($validator, 'query');
        $service = $this->readStringParameter($validator, 'service');
        $provider = $this->readStringParameter($validator, 'provider');
        $date = $this->readStringParameter($validator, 'date');

        $page = $this->readNumberParameter($validator, 'page', 1);
        $userAction = $this->readNumberParameter($validator, 'user', 0);
        $perPage = $this->readNumberParameter($validator, 'perPage', 100);
        $hideNavigation = $this->readNumberParameter($validator, 'hideNavigation', 0);

        return [
            'queryString' => $queryString,
            'page' => $page,
            'service' => $service,
            'provider' => $provider,
            'date' => $date,
            'userAction' => $userAction,
            'perPage' => $perPage,
            'hideNavigation' => $hideNavigation,
            'isSearchRequested' => (
                trim($queryString) !== ''
                || trim($service) !== ''
                || trim($provider) !== ''
                || trim($date) !== ''
                || $userAction !== 0
            ),
        ];
    }

    private function readProcessList(bool $isSearchRequested, string $queryString): ProcessList
    {
        if (!$isSearchRequested || '' === trim($queryString)) {
            return new ProcessList();
        }

        return \App::$http
            ->readGetResult('/process/search/', [
                'query' => $queryString,
                'resolveReferences' => 1,
            ])
            ->getCollection();
    }
}
