<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Helper;
use BO\Slim\Render;
use BO\Zmsadmin\Exception\BadRequest;
use BO\Zmsadmin\Exception\NotAllowed;
use BO\Zmsentities\Collection\DepartmentList;
use BO\Zmsentities\Department;
use BO\Zmsentities\Exception\UserAccountAccessRightsFailed;
use BO\Zmsentities\Helper\Property;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

/**
 * returning Signatures for signing requests
 */
class UrlParameterSigning extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param SlimRequest $request
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $data = $validator->getInput()->isJson()->assertValid()->getValue();

        if (!isset($data['section']) || !isset($data['parameters'])) {
            throw new BadRequest();
        }

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
        $collections = isset($data['parameters']['collections']) ? $data['parameters']['collections'] : [];

        if ((isset($collections['scopelist']) && strlen($collections['scopelist']) > 0
                || isset($collections['clusterlist']) && strlen($collections['clusterlist']))
            && isset($workstation['scope']['id'])
            && !Validator::value($workstation['scope']['id'])->isNumber()->hasFailed()
        ) {
            $organisation = \App::$http->readGetResult(
                '/scope/'. $workstation['scope']['id'] .'/organisation/',
                ['resolveReferences' => 3]
            )->getEntity();

            $scopeIds   = [];
            $clusterIds = [];

            foreach ($organisation->departments as $departmentData) {
                $department = (new Department($departmentData))->withCompleteScopeList();
                if (Property::__keyExists('scopes', $department)) {
                    /** @var \BO\Zmsentities\Scope $scope */
                    foreach ($department['scopes'] as $scope) {
                        $scopeIds[$scope['id']] = $scope['id'];
                    }
                }
                if (Property::__keyExists('clusters', $department)) {
                    /** @var \BO\Zmsentities\Cluster $scope */
                    foreach ($department['clusters'] as $cluster) {
                        $clusterIds[$cluster['id']] = $cluster['id'];
                    }
                }
            }

            if (isset($collections['scopelist']) && strlen($collections['scopelist']) > 0) {
                $requestedIds = explode(',', $collections['scopelist']);
                if (count(array_diff($requestedIds, $scopeIds)) > 0) {
                    throw new UserAccountAccessRightsFailed();
                }
            }

            if (isset($collections['clusterlist']) && strlen($collections['clusterlist']) > 0) {
                $requestedIds = explode(',', $collections['clusterlist']);
                if (count(array_diff($requestedIds, $clusterIds)) > 0) {
                    throw new UserAccountAccessRightsFailed();
                }
            }
        }

        $data['hmac'] = Helper::hashQueryParameters($data['section'], $data['parameters'], ['collections', 'queue']);

        return Render::withJson($response, $data);
    }
}
