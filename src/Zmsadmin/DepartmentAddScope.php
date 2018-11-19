<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class DepartmentAddScope extends Scope
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $currentSource = $this->readCurrentSource($workstation->getScope()->getSource());
        $departmentId = Validator::value($args['id'])->isNumber()->getValue();
        $department = \App::$http
            ->readGetResult('/department/'. $departmentId .'/', ['resolveReferences' => 0])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $departmentId . '/organisation/')->getEntity();
        $input = $request->getParsedBody();

        if (is_array($input) && array_key_exists('save', $input)) {
            $result = $this->testUpdateEntity($input, $department->id);
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
            'sourceList' => $this->readSourceList(),
            'source' => $currentSource,
            'exception' => (isset($result)) ? $result : null,
            'provider' => $workstation->getScope()->provider,
            'providerList' => Helper\ProviderHandler::readProviderList($workstation->getScope()->getSource())
        ));
    }
}
