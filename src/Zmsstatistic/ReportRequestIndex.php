<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class ReportRequestIndex extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('date')->isString()->getValue();
        $selectedDate = ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d');

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
            'page/reportRequestIndex.twig',
            array(
                'title' => 'Dienstleistungsstatistik',
                'menuActive' => 'request',
                'selectedDate' => $selectedDate,
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
