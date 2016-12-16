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
        $calldisplay = new \BO\Zmsentities\Calldisplay($input);
        $queueList = new Collection();

        if ($calldisplay->hasScopeList()) {
            $scopeQuery = new Scope();
            foreach ($calldisplay->getScopeList() as $scope) {
                $scope = $scopeQuery->readEntity($scope->id, $resolveReferences - 1);
                if (! $scope) {
                    throw new Exception\Scope\ScopeNotFound();
                }
                $queueList->addList($scopeQuery->readWithWaitingTime($scope->id, \App::$now));
            }
        }
        if ($calldisplay->hasClusterList()) {
            $clusterQuery = new Cluster();
            foreach ($calldisplay->getClusterList() as $cluster) {
                $cluster = $clusterQuery->readEntity($cluster->id, $resolveReferences - 1);
                if (! $cluster) {
                    throw new Exception\Cluster\ClusterNotFound();
                }
                $queueList->addList($clusterQuery->readQueueList($cluster->id, \App::$now));
            }
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $queueList->withSortedArrival();
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
