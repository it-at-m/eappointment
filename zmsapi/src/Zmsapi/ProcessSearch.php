<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;

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
    ) {
        $workstation = (new Helper\User($request, 2))->checkPermissions('customersearch');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $lessResolvedData = Validator::param('lessResolvedData')->isNumber()->setDefault(0)->getValue();
        $page = Validator::param('page')->isNumber()->setDefault(1)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(100)->getValue();
        if ($limit > 1000) {
            $limit = 1000;
        }
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $parameters = $request->getParams();
        unset($parameters['resolveReferences']);
        unset($parameters['lessResolvedData']);
        unset($parameters['limit']);
        unset($parameters['page']);
        $parameters['upcomingOnly'] = 1;

        if (!$workstation->getUseraccount()->isSuperUser()) {
            $scopeIds = $workstation->getUseraccount()
                ->getDepartmentList()
                ->getUniqueScopeList()
                ->getIds();
            $parameters['scopeIds'] = implode(',', $scopeIds);
        }

        $processQuery = new Process();
        $totalCount = $processQuery->readSearchCount($parameters);
        $processList = $processQuery->readSearch($parameters, $resolveReferences, $limit, $offset);
        if ($lessResolvedData) {
            $processList = $processList->withLessData();
        }

        $message = Response\Message::create($request);
        $message->data = $processList->withAccess($workstation->getUseraccount());
        $message->meta->totalCount = $totalCount;
        $message->meta->page = $page;
        $message->meta->limit = $limit;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
