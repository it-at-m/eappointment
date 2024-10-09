<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use App;
use BO\Slim\Render;
use \BO\Zmsentities\Collection\UseraccountList as Collection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UseraccountByDepartment extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $departmentId = $args['id'];

        $workstation = App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $department = App::$http->readGetResult("/department/$departmentId/")->getEntity();
        $useraccountList = App::$http->readGetResult(
            "/department/$departmentId/useraccount/",
            ['resolveReferences' => 1]
        )->getCollection();
        $workstationList = App::$http->readGetResult(
            "/department/$departmentId/workstation/",
            ['resolveReferences' => 0]
        )->getCollection();
        $scopeList = App::$http->readGetResult('/scope/', ['resolveReferences' => 0])->getCollection();

        /** @var Workstation $workstationItem */
        foreach ($workstationList as $workstationItem) {
            $workstationItem->scope = $scopeList->getEntity($workstationItem->getScope()->getId());
        }

        $ownerList = App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();

        return Render::withHtml(
            $response,
            'page/useraccount.twig',
            array(
                'title' => 'Nutzer',
                'menuActive' => 'useraccount',
                'workstation' => $workstation,
                'department' => $department,
                'workstationList' => $workstationList,
                'useraccountList' => ($useraccountList) ?
                    $useraccountList->sortByCustomStringKey('id') :
                    new Collection(),
                'ownerlist' => $ownerList,
            )
        );
    }
}
