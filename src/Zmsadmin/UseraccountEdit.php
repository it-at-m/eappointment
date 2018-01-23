<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;

class UseraccountEdit extends BaseController
{

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $userAccountName = Validator::value($args['loginname'])->isString()->getValue();
        $confirm_success = $request->getAttribute('validator')->getParameter('confirm_success')->isString()->getValue();
        $userAccount = \App::$http->readGetResult('/useraccount/'. $userAccountName .'/')->getEntity();
        $workstation->getUseraccount()->hasEditAccess($userAccount);
        $ownerList = \App::$http->readGetResult('/owner/', ['resolveReferences' => 2])->getCollection();

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('id', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity = \App::$http->readPostResult('/useraccount/'. $userAccountName .'/', $entity)->getEntity();

            return \BO\Slim\Render::redirect(
                'useraccountEdit',
                array(
                    'loginname' => $entity->id
                ),
                array(
                    'confirm_success' => \App::$now->getTimeStamp()
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountEdit.twig',
            array(
                'debug' => \App::DEBUG,
                'userAccount' => $userAccount,
                'confirm_success' => $confirm_success,
                'ownerList' => $ownerList ? $ownerList->toDepartmentListByOrganisationName() : [],
                'workstation' => $workstation,
                'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount'
            )
        );
    }
}
