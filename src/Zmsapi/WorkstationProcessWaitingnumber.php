<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\Cluster;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationProcessWaitingnumber extends BaseController
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
        $workstation = (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = $workstation->scope;
        $scope = (new \BO\Zmsdb\Scope)->readEntity($workstation->scope['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = (new Cluster())->readByScopeId($scope->id, $resolveReferences);
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
            $scope = (new Cluster())->readScopeWithShortestWaitingTime($cluster->id, \App::$now);
        }

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->scope = $scope;
        $process = ProcessStatusQueued::init()->writeNewFromAdmin($process, \App::$now);
        $process->testValid();
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
