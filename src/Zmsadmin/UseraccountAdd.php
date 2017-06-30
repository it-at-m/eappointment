<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;
use \BO\Zmsadmin\Helper\UseraccountForm;

class UseraccountAdd extends BaseController
{
    /**
     * @SuppressWarnings(unused)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $input = $request->getParsedBody();
        $ownerList = \App::$http->readGetResult('/owner/', ['resolveReferences' => 2])->getCollection();
        $formData = null;

        if (is_array($input) && array_key_exists('save', $input)) {
            $form = UseraccountForm::fromAddParameters();
            $formData = $form->getStatus();
            if ($formData && ! $form->hasFailed()) {
                $input['password'] = $input['changePassword'][0];
                $entity = (new Entity($input))->withCleanedUpFormData();
                $entity = $entity->withDepartmentList();
                $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
                return \BO\Slim\Render::redirect(
                    'useraccount',
                    array(),
                    array(
                        'success' => 'useraccount_created'
                    )
                );
            }
        }

        return \BO\Slim\Render::withHtml($response, 'page/useraccountEdit.twig', array(
            'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
            'workstation' => $workstation,
            'formdata' => $formData,
            'action' => 'add',
            'title' => 'Nutzer: Einrichtung und Administration',
            'menuActive' => 'useraccount'
        ));
    }
}
