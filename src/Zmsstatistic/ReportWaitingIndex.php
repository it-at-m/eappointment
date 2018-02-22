<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportWaitingIndex extends BaseController
{
    protected $hashset = [
        'waitingcount',
        'waitingtime',
        'waitingcalculated'
    ];

    protected $groupfields = [
        'date',
        'hour'
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
        $waitingPeriod = \App::$http
          ->readGetResult('/warehouse/waitingscope/' . $this->workstation->scope['id'] . '/')
          ->getEntity();
        $exchangeWaiting = null;
        if (isset($args['period'])) {
            $exchangeWaiting = \App::$http
            ->readGetResult('/warehouse/waitingscope/' . $this->workstation->scope['id'] . '/'. $args['period']. '/')
            ->getEntity()
            ->toGrouped($this->groupfields, $this->hashset)
            ->withMaxByHour($this->hashset)
            ->withMaxAndAverageFromWaitingTime();
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'waitingscope';
            $args['reports'][] = $exchangeWaiting;
            $args['scope'] = $this->workstation->scope;
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            return (new Download\WaitingReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reportWaitingIndex.twig',
            array(
              'title' => 'Wartestatistik Standort',
              'activeScope' => 'active',
              'menuActive' => 'waiting',
              'department' => $this->department,
              'organisation' => $this->organisation,
              'waitingPeriod' => $waitingPeriod,
              'showAll' => 1,
              'period' => $args['period'],
              'exchangeWaiting' => $exchangeWaiting,
              'source' => ['entity' => 'WaitingIndex'],
              'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
