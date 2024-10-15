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
         $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 2))->getCollection();
 
         if ($workstation->hasSuperUseraccount()) {
             $useraccountList = \App::$http->readGetResult("/role/$roleLevel/useraccount/")->getCollection();
         } else {
             $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
             $ownersDepartmentIds = $workstation->getUseraccount()->getDepartmentList()->getIds();
             $collection = \App::$http->readGetResult("/role/$roleLevel/useraccount/")->getCollection();

             $useraccountList = [];
             foreach ($collection as $useraccount) {
                 if (isset($useraccount->rights['superuser']) && $useraccount->rights['superuser'] === "1") {
                     continue;
                 }
                 if (isset($useraccount->departments)) {
                     foreach ($useraccount->departments as $department) {
                         if (in_array($department->id, $ownersDepartmentIds)) {
                             $useraccountList[] = $useraccount;
                             break;
                         }
                     }
                 }
             }
         }
 
         return \BO\Slim\Render::withHtml(
             $response,
             'page/useraccount.twig',
             array(
                 'title' => 'Nutzer',
                 'roleLevel' => $roleLevel,
                 'menuActive' => 'useraccount',
                 'workstation' => $workstation,
                 'useraccountListByRole' => ($useraccountList) ?
                 $useraccountList :
                 new Collection(),
                 'ownerlist' => $ownerList,
             )
         );
     }
 }
 
