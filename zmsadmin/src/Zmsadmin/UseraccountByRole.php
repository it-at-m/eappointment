<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\UseraccountList as Collection;

class UseraccountByRole extends BaseController
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
        $roleLevel = $args['id'];
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();

        if ($workstation->hasSuperUseraccount()) {
            $useraccountList = \App::$http->readGetResult("/role/$roleLevel/useraccount/")->getCollection();
        } else {
            $useraccountList = [];
        }

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'roleLevel' => $roleLevel,
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'useraccountListByRole' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'ownerlist' => $ownerList,
            )
        );
    }
}
