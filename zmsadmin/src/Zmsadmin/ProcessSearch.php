<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Collection\ProcessList;
use DateTime;

/**
  * Handle requests concerning services
  *
  */
class ProcessSearch extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();
        $page = $validator->getParameter('page')
            ->isNumber()
            ->setDefault(1)
            ->getValue();
        $service = $validator->getParameter('service')
            ->isString()
            ->setDefault('')
            ->getValue();
        $provider = $validator->getParameter('provider')
            ->isString()
            ->setDefault('')
            ->getValue();
        $date = $validator->getParameter('date')
            ->isString()
            ->setDefault(null)
            ->getValue();
        $userAction = $validator->getParameter('user')
            ->isNumber()
            ->setDefault(0)
            ->getValue();
        $perPage = $validator->getParameter('perPage')
            ->isNumber()
            ->setDefault(100)
            ->getValue();
        $processList = !empty($queryString) ? \App::$http->readGetResult('/process/search/', [
            'query' => $queryString,
            'resolveReferences' => 1,
        ])->getCollection() : new ProcessList();

        $scopeIds = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList()->getIds();
        if (!empty($processList) && !$workstation->hasSuperUseraccount()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        if ($workstation->hasAuditAccount()) {
            $queryString = urlencode($queryString);
            $logList = \App::$http
                ->readGetResult("/log/process/", [
                        'searchQuery' => $queryString,
                        'page' => $page,
                        'perPage' => $perPage,
                        'service' => $service ? trim($service) : null,
                        'provider' => $provider ? trim($provider) : null,
                        'userAction' => (int) $userAction,
                        'date' => $date
                    ])
                ->getCollection();
            $logList = $this->filterLogListForUserRights($logList, $scopeIds);
        }

        $processListOther = new \BO\Zmsentities\Collection\ProcessList();
        if (!$workstation->hasSuperUseraccount()) {
            $processListOther = $processList->withOutScopeId($workstation->scope['id']);
            $processList = $processList->withScopeId($workstation->scope['id']);
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/search.twig',
            array(
                'title' => 'Suche',
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
                'searchProcessQuery' => urldecode($queryString),
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
}
