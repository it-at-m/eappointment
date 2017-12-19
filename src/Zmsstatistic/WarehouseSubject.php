<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class WarehouseSubject extends BaseController
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
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
        $subjectList = \App::$http->readGetResult('/warehouse/'. $args['subject'] .'/')->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseSubject.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'dictionary' => $subjectList->dictionary,
                'subjectList' => $subjectList->toHashed(),
                'category' => $args['subject'],
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
