<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        $processList = \App::$http->readGetResult('/process/search/', [
            'query' => $queryString,
            'resolveReferences' => 1,
        ])->getCollection();

        $scopeIds = $workstation->getUseraccount()->getDepartmentList()->getUniqueScopeList()->getIds();
        if (!$workstation->hasSuperUseraccount()) {
            $processList = $this->filterProcessListForUserRights($processList, $scopeIds);
        }

        $processList = $processList ? $processList : new \BO\Zmsentities\Collection\ProcessList();
        if ($workstation->hasAuditAccount()) {
            $queryString = urlencode($queryString);
            $logList = \App::$http->readGetResult("/log/process/$queryString/")->getCollection();
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
