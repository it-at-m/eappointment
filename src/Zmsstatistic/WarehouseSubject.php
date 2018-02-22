<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsstatistic\Download\WarehouseSubject as Download;

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

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseSubject.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'dictionary' => $subjectList->dictionary,
                'subjectList' => $subjectList->toHashed(),
                'category' => $args['subject'],
                'categoryName' => Download::$subjectTranslations[$args['subject']],
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
