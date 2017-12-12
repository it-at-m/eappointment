<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportClientScope extends BaseController
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
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' .$department->id . '/organisation/')->getEntity();
        $scopeId = (isset($args['id'])) ? $args['id'] : $workstation->scope['id'];

        $clientPeriod = \App::$http
          ->readGetResult('/warehouse/clientscope/' . $scopeId . '/')
          ->getEntity();

        $exchangeClient = null;
        $exchangeNotification = null;
        if (isset($args['period'])) {
            $exchangeClient = \App::$http
              ->readGetResult('/warehouse/clientscope/' . $scopeId . '/'. $args['period']. '/')
              ->getEntity()
              ->withCalculatedTotals($this->totals)
              ->getJoinedHashData();

            $exchangeNotification = \App::$http
              ->readGetResult('/warehouse/notificationscope/' . $scopeId . '/'. $args['period']. '/')
              ->getEntity()
              ->getJoinedHashData();
        }

        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportClientIndex.twig',
            array(
                'title' => 'Kundenstatistik Standort',
                'activeScope' => 'active',
                'menuActive' => 'client',
                'department' => $department,
                'organisation' => $organisation,
                'clientperiod' => $clientPeriod,
                'showAll' => 1,
                'period' => $args['period'],
                'exchangeClient' => $exchangeClient,
                'exchangeNotification' => $exchangeNotification,
                'source' => ['entity' => 'ScopeClient', 'id' => $scopeId],
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
