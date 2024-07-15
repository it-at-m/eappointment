<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportWaitingOrganisation extends BaseController
{
    protected $hashset = [
        'waitingcount',
        'waitingtime',
        'waitingcalculated',
        'waitingcount_termin',
        'waitingtime_termin',
        'waitingcalculated_termin',
        'waytime',
        'waytime_termin',        
    ];

    protected $groupfields = [
        'date',
        'hour'
    ];

    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $waitingPeriod = \App::$http
          ->readGetResult('/warehouse/waitingorganisation/' . $this->organisation->id . '/')
          ->getEntity();
        $exchangeWaiting = null;
        if (isset($args['period'])) {
            $exchangeWaiting = \App::$http
            ->readGetResult('/warehouse/waitingorganisation/' . $this->organisation->id . '/'. $args['period']. '/')
            ->getEntity()
            ->toGrouped($this->groupfields, $this->hashset)
            ->withMaxByHour($this->hashset)
            ->withMaxAndAverageFromWaitingTime();

            $exchangeWaiting = $this->withMaxAndAverageFromWaitingTime($exchangeWaiting, 'waitingtime');
            $exchangeWaiting = $this->withMaxAndAverageFromWaitingTime($exchangeWaiting, 'waitingtime_termin');
            $exchangeWaiting = $this->withMaxAndAverageFromWaitingTime($exchangeWaiting, 'waytime');
            $exchangeWaiting = $this->withMaxAndAverageFromWaitingTime($exchangeWaiting, 'waytime_termin');            
        }

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'waitingscope';
            $args['reports'][] = $exchangeWaiting;
            $args['organisation'] = $this->organisation;
            return (new Download\WaitingReport(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }

        return Render::withHtml(
            $response,
            'page/reportWaitingIndex.twig',
            array(
              'title' => 'Wartestatistik Bezirk',
              'activeOrganisation' => 'active',
              'menuActive' => 'waiting',
              'department' => $this->department,
              'organisation' => $this->organisation,
              'waitingPeriod' => $waitingPeriod,
              'showAll' => 1,
              'period' => (isset($args['period'])) ? $args['period'] : null,
              'exchangeWaiting' => $exchangeWaiting,
              'source' => ['entity' => 'WaitingOrganisation'],
              'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }

    public function withMaxAndAverageFromWaitingTime($entity, $targetKey)
    {
        foreach ($entity->data as $date => $dateItems) {
            $maxima = 0;
            $total = 0;
            $count = 0;
            foreach ($dateItems as $hourItems) {
                if (is_array($hourItems)) { // Check if $hourItems is an array
                    foreach ($hourItems as $key => $value) {
                        if (is_numeric($value) && $targetKey == $key && 0 < $value) {
                            $total += $value;
                            $count += 1;
                            $maxima = ($maxima > $value) ? $maxima : $value;
                        }
                    }
                }
            }
            $entity->data[$date]['max_' . $targetKey] = $maxima;
            $entity->data[$date]['average_' . $targetKey] = (! $total || ! $count) ? 0 : floor($total / $count);
        }
        return $entity;
    }
}
