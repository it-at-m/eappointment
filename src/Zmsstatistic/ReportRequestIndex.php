<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportRequestIndex extends BaseController
{
    protected $hashset = [
        'requestscount'
    ];

    protected $groupfields = [
        'name',
        'date'
    ];

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                  'error' => 'login_failed'
                )
            );
        }
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' .$department->id . '/organisation/')->getEntity();
        $requestPeriod = \App::$http
          ->readGetResult('/warehouse/requestscope/' . $workstation->scope['id'] . '/')
          ->getEntity();
        $exchangeRequest = null;
        if (isset($args['period'])) {
            $exchangeRequest = \App::$http
            ->readGetResult('/warehouse/requestscope/' . $workstation->scope['id'] . '/'. $args['period']. '/')
            ->getEntity()
            ->toGrouped($this->groupfields, $this->hashset)
            ->withRequestsSum();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'requestscope';
            $args['reports'][] = $exchangeRequest;
            $args['scope'] = $workstation->scope;
            $args['department'] = $department;
            $args['organisation'] = $organisation;
            return (new Download\RequestReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportRequestIndex.twig',
            array(
              'title' => 'Dienstleistungsstatistik Standort',
              'activeScope' => 'active',
              'menuActive' => 'request',
              'department' => $department,
              'organisation' => $organisation,
              'requestPeriod' => $requestPeriod,
              'showAll' => 1,
              'period' => $args['period'],
              'exchangeRequest' => $exchangeRequest,
              'source' => ['entity' => 'RequestIndex'],
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
