<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsapi\Helper\SearchPagination;
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

        $requestedPage = (int) (Validator::param('page')->isNumber()->setDefault(1)->getValue() ?? 1);
        $requestedLimit = (int) (
            Validator::param('limit')->isNumber()->setDefault(SearchPagination::DEFAULT_RESULTS_PER_PAGE)->getValue()
            ?? SearchPagination::DEFAULT_RESULTS_PER_PAGE
        );
        $page = SearchPagination::normalizePage($requestedPage);
        $resultsPerPage = SearchPagination::normalizeResultsPerPage($requestedLimit);
        $offset = SearchPagination::offset($page, $resultsPerPage);

        $parameters = $request->getParams();
        unset($parameters['resolveReferences']);
        unset($parameters['lessResolvedData']);
        unset($parameters['limit']);
        unset($parameters['page']);
        $parameters['upcomingOnly'] = 1;

        foreach (['service', 'provider', 'date'] as $filterKey) {
            if (!isset($parameters[$filterKey]) || trim((string) $parameters[$filterKey]) === '') {
                unset($parameters[$filterKey]);
            }
        }

        if (!$workstation->getUseraccount()->isSuperUser()) {
            $scopeIds = $workstation->getUseraccount()
                ->getDepartmentList()
                ->getUniqueScopeList()
                ->getIds();
            $parameters['scopeIds'] = implode(',', $scopeIds);
        }

        $processQuery = new Process();
        $totalCount = $processQuery->readSearchCount($parameters);
        $processList = $processQuery->readSearch($parameters, $resolveReferences, $resultsPerPage, $offset);
        if ($lessResolvedData) {
            $processList = $processList->withLessData();
        }

        $message = Response\Message::create($request);
        $message->data = $processList->withAccess($workstation->getUseraccount());
        $message->meta->totalCount = $totalCount;
        $message->meta->page = $page;
        $message->meta->limit = $resultsPerPage;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
