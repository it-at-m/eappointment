<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsapi\Helper\SearchPagination;
use BO\Zmsdb\Log as Query;
use DateTime;

class ProcessLog extends BaseController
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
        (new Helper\User($request))->checkPermissions('logs');
        $searchQuery = Validator::param('searchQuery')->isString()->setDefault(null)->getValue();
        $service = Validator::param('service')->isString()->setDefault(null)->getValue();
        $provider = Validator::param('provider')->isString()->setDefault(null)->getValue();
        $date = Validator::param('date')->isString()->setDefault(null)->getValue();
        $userAction = Validator::param('userAction')->isNumber()->setDefault(0)->getValue();
        $requestedPage = (int) Validator::param('page')->isNumber()->setDefault(1)->getValue();
        $requestedResultsPerPage = (int) Validator::param('perPage')
            ->isNumber()
            ->setDefault(SearchPagination::DEFAULT_RESULTS_PER_PAGE)
            ->getValue();
        $page = SearchPagination::normalizePage($requestedPage);
        $resultsPerPage = SearchPagination::normalizeResultsPerPage($requestedResultsPerPage);
        $scopeIds = Validator::param('scopeIds')->isString()->setDefault(null)->getValue();

        $resolvedScopeIds = null;
        if ($scopeIds !== null && $scopeIds !== '') {
            $resolvedScopeIds = array_values(array_filter(array_map('intval', explode(',', $scopeIds))));
        }

        $logList = (new Query())->readByProcessData(
            urldecode((string) $searchQuery),
            $service,
            $provider,
            $date ? new DateTime($date) : null,
            $userAction,
            $page,
            $resultsPerPage,
            $resolvedScopeIds
        );

        $message = Response\Message::create($request);
        $message->data = $logList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
