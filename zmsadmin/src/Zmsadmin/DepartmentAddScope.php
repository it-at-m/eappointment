<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Scope as Entity;
use BO\Zmsentities\Exception\UserAccountMissingRights;

/**
  * Handle requests concerning services
  *
  */
class DepartmentAddScope extends Scope
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['scope'])) {
            throw new UserAccountMissingRights();
        }
        $departmentId = Validator::value($args['id'])->isNumber()->getValue();
        $department = \App::$http
            ->readGetResult('/department/' . $departmentId . '/', ['resolveReferences' => 0])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $departmentId . '/organisation/')->getEntity();
        $providerList = Helper\ProviderHandler::readProviderList($workstation->getScope()->getSource());
        $sourceList = $this->readSourceList();
        $input = $request->getParsedBody();

        if (is_array($input) && array_key_exists('save', $input)) {
            $result = $this->writeUpdatedEntity($input, $department->id, null, $workstation);
            if ($result instanceof Entity) {
                $this->writeUploadedImage($request, $result->id, $input);
                return \BO\Slim\Render::redirect('scope', ['id' => $result->id], [
                    'success' => 'scope_created'
                ]);
            }
        }
        return \BO\Slim\Render::withHtml($response, 'page/scope.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner',
            'workstation' => $workstation,
            'organisation' => $organisation,
            'department' => $department,
            'sourceList' => $sourceList,
            'exception' => (isset($result)) ? $result : null,
            'providerList' => $providerList
        ));
    }
}
