<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Download\WarehouseSubject as Download;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WarehouseSubject extends BaseController
{
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
        $subjectList = \App::$http->readGetResult('/warehouse/'. $args['subject'] .'/')->getEntity();

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'raw-'.$args['subject'];
            $args['reports'][] = $subjectList;
            $args['department'] = $this->department;
            $args['organisation'] = $this->organisation;
            return (new Download(\App::$slim->getContainer()))->readResponse($request, $response, $args);
        }
        if (count($subjectList['data']) == 1) {
            return Render::redirect("WarehousePeriod", [
                'subject' => $args['subject'],
                'subjectid' => $subjectList['data'][0][0],
            ]);
        }

        return Render::withHtml(
            $response,
            'page/warehouseSubject.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'dictionary' => $subjectList->dictionary,
                'subjectList' => $subjectList->toHashed(),
                'category' => $args['subject'],
                'categoryName' => $subjectList['title'],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
