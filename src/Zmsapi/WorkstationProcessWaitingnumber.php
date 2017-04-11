<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Cluster;

class WorkstationProcessWaitingnumber extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = Helper\User::checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = $workstation->scope;
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = (new Cluster())->readByScopeId($workstation->scope['id'], $resolveReferences);
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
            $scope = (new Cluster())->readScopeWithShortestWaitingTime($cluster->id, \App::$now);
        }

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->scope = $scope;
        $process = (new Process())->writeNewFromAdmin($process, \App::$now);

        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
