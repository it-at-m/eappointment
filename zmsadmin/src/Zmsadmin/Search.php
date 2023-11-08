<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class Search extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();
        $processList = \App::$http->readGetResult('/process/search/', [
            'query' => $queryString,
            'resolveReferences' => 1,
        ])->getCollection();
        $processList = $processList ? $processList : new \BO\Zmsentities\Collection\ProcessList();
        if (preg_match('#^\d{4,}$#', $queryString) && $workstation->hasSuperUseraccount()) {
            $logList = \App::$http->readGetResult("/log/process/$queryString/")->getCollection();
        }
        if (!isset($logList) || !$logList) {
            $logList = new \BO\Zmsentities\Collection\LogList();
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
                'logList' => $logList,
                'searchQuery' => $queryString,
                'menuActive' => 'search'
            )
        );
    }
}
