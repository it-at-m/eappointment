<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Collection\ProcessList as ProcessListCollection;

class ProcessListByClusterAndDate extends BaseController
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
        (new Helper\User($request))->checkRights('basic');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $dateTime = new \BO\Zmsentities\Helper\DateTime($args['date']);
        $dateTime = $dateTime->modify(\App::$now->format('H:i'));

        $query = new Query();
        $cluster = $query->readEntity($args['id'], 0);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $queueList = $query->readQueueList($cluster->id, $dateTime, $resolveReferences ? $resolveReferences + 1 : 1);
        $allArchivedProcesses = new ProcessListCollection();
        foreach ($cluster->scopes as $scope) {
            $archivedProcesses =
                (new ProcessStatusArchived())->readListByScopeAndDate($scope->id, $dateTime);

            if ($archivedProcesses instanceof ProcessListCollection) {
                foreach ($archivedProcesses as $process) {
                    $allArchivedProcesses[] = $process;
                }
            } else {
                error_log("Expected ProcessListCollection, received " . gettype($archivedProcesses));
            }
        }
        $message = Response\Message::create($request);
        $message->data = $queueList->toProcessList()->withResolveLevel($resolveReferences);

        // Add all archived processes to the response data
        $message->data->addData($allArchivedProcesses);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
