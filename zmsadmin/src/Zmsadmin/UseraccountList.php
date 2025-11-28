<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountList extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
        $validator = $request->getAttribute('validator');
        $queryString = $validator->getParameter('query')
            ->isString()
            ->getValue();

        $useraccountList = new Collection();
        if ($workstation->hasSuperUseraccount()) {
            $params = ["resolveReferences" => 0];
            if ($queryString) {
                $params['query'] = $queryString;
            }
            $useraccountList = \App::$http->readGetResult("/useraccount/", $params)->getCollection();
        } else {
            $departmentListIds = $workstation->getUseraccount()->getDepartmentList()->getIds();

            $params = ['resolveReferences' => 0];
            if ($queryString) {
                $params['query'] = $queryString;
            }
            $useraccountList = \App::$http
                ->readGetResult('/department/' . implode(',', $departmentListIds) . '/useraccount/', $params)
                ->getCollection();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountList' => $useraccountList,
                'searchUserQuery' => $queryString,
                'ownerlist' => $ownerList,
                'success' => $success,
            )
        );
    }
}
