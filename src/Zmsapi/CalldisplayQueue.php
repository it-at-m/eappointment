<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope;
use \BO\Zmsdb\Cluster;
use \BO\Zmsentities\Collection\QueueList as Collection;

/**
  * Handle requests concerning services
  */
class CalldisplayQueue extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calldisplay = (new \BO\Zmsentities\Calldisplay($input))->withOutClusterDuplicates();
        $queueList = new Collection();

        if ($calldisplay->hasScopeList()) {
            foreach ($calldisplay->getScopeList() as $scope) {
                $queueList->addList(static::getCalculatedQueueListFromScope($scope, $resolveReferences));
            }
        }
        if ($calldisplay->hasClusterList()) {
            $clusterQuery = new Cluster();
            foreach ($calldisplay->getClusterList() as $cluster) {
                $cluster = $clusterQuery->readEntity($cluster->id, $resolveReferences);
                if (! $cluster) {
                    throw new Exception\Cluster\ClusterNotFound();
                }
                if ($cluster->scopes->count()) {
                    foreach ($cluster->scopes as $scope) {
                        $queueList->addList(static::getCalculatedQueueListFromScope($scope, $resolveReferences));
                    }
                }
            }
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $queueList->withoutDublicates();
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected static function getCalculatedQueueListFromScope($scope, $resolveReferences)
    {
        $scopeQuery = new Scope();
        $scope = $scopeQuery->readEntity($scope->id, $resolveReferences - 1);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $scope = $scopeQuery->readWithWorkstationCount($scope->id, \App::$now);
        return $scopeQuery
            ->readQueueListWithWaitingTime($scope, \App::$now)
            ->withPickupDestination($scope);
    }
}
