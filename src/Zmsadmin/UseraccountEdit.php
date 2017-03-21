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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $userAccountName = Validator::value($args['loginname'])->isString()->getValue();
        $userAccount = \App::$http->readGetResult('/useraccount/'. $userAccountName .'/')->getEntity();
        $workstation->getUseraccount()->hasEditAccess($userAccount);

        $ownerList = \App::$http->readGetResult('/owner/')->getCollection();

        if (null === $userAccount || !$userAccount->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

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
                        'success' => 'useraccount_updated'
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
                'formdata' => $formData,
                'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
                'workstation' => $workstation,
                'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount'
            )
        );
    }
}
