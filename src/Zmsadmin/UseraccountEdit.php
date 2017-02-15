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
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $userAccountName = Validator::value($args['loginname'])->isString()->getValue();
        $userAccount = \App::$http->readGetResult('/useraccount/'. $userAccountName .'/')->getEntity();
        $ownerList = \App::$http->readGetResult('/owner/')->getCollection();

        if (null === $userAccount || !$userAccount->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $userAccount = new Entity($input);
            $userAccount->id = $userAccountName;
            $userAccount = \App::$http->readPostResult(
                '/useraccount/'. $userAccount->id .'/',
                $userAccount
            )->getEntity();
        }

        \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountEdit.twig',
            array (
                'userAccount' => $userAccount,
                'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
                'workstation' => $workstation,
                'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount'
            )
        );
    }
}
