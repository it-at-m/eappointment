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
        $organisation = \App::$http->readGetResult('/department/' . $department->id . '/organisation/')->getEntity();
        $subjectList = \App::$http->readGetResult('/warehouse/'. $args['subject'] .'/')->getEntity();

        $type = $validator->getParameter('type')->isString()->getValue();
        if ($type) {
            $args['category'] = 'raw-'.$args['subject'];
            $args['reports'][] = $subjectList;
            $args['department'] = $department;
            $args['organisation'] = $organisation;
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
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
