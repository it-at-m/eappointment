<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportRequestOrganisation extends BaseController
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
        $organisation = \App::$http->readGetResult('/department/'. $department->id .'/organisation/')->getEntity();
        $requestPeriod = \App::$http
          ->readGetResult('/warehouse/requestorganisation/' . $organisation->id . '/')
          ->getEntity();
        $exchangeRequest = null;
        if (isset($args['period'])) {
            $exchangeRequest = \App::$http
            ->readGetResult('/warehouse/requestorganisation/' . $organisation->id . '/'. $args['period']. '/')
            ->getEntity()
            ->toGrouped($this->groupfields, $this->hashset)
            ->withRequestsSum();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'requestorganisation';
            $args['reports'][] = $exchangeRequest;
            $args['organisation'] = $organisation;
            return (new Download\RequestReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportRequestIndex.twig',
            array(
              'title' => 'Dienstleistungsstatistik Bezirk',
              'activeOrganisation' => 'active',
              'menuActive' => 'request',
              'department' => $department,
              'organisation' => $organisation,
              'requestPeriod' => $requestPeriod,
              'showAll' => 1,
              'period' => $args['period'],
              'exchangeRequest' => $exchangeRequest,
              'source' => ['entity' => 'RequestOrganisation'],
              'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
