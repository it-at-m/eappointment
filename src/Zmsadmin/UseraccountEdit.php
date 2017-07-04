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
use \BO\Zmsadmin\Helper\UseraccountForm;

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

        $formData = null;
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $form = UseraccountForm::fromAddParameters();
            $formData = $form->getStatus();
            if ($formData && ! $form->hasFailed()) {
                $entity = new Entity($input);
                $entity = $entity->withDepartmentList()->withCleanedUpFormData();
                $entity->id = $userAccountName;
                $entity = \App::$http->readPostResult(
                    '/useraccount/'. $userAccount->id .'/',
                    $entity
                )->getEntity();
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
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountEdit.twig',
            array(
                'debug' => \App::DEBUG,
                'userAccount' => $userAccount,
                'confirm_success' => $confirm_success,
                'formdata' => $formData,
                'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
                'workstation' => $workstation,
                'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount'
            )
        );
    }
}
