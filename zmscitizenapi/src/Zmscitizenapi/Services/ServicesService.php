<?php

namespace BO\Zmscitizenapi\Services;

class ServicesService
{
    public function getServices()
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestList = $sources->getRequestList() ?? [];
        $servicesProjectionList = [];

        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            $servicesProjectionList[] = [
                "id" => $request->getId(),
                "name" => $request->getName(),
                "maxQuantity" => $additionalData['maxQuantity'] ?? 1,
            ];
        }

        return $servicesProjectionList;
    }

    public function getServicesByOfficeIds(array $officeIds)
    {
        $officeIds = array_unique($officeIds);

        if (empty($officeIds) || $officeIds == ['']) {
            return [
                'services' => [],
                'error' => 'Invalid officeId(s)',
                'status' => 400,
            ];
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestList = $sources->getRequestList();
        $requestRelationList = $sources->getRequestRelationList();

        $services = [];
        $notFoundIds = [];
        $addedServices = [];

        foreach ($officeIds as $officeId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->provider->id == $officeId) {
                    foreach ($requestList as $request) {
                        if ($request->id == $relation->request->id && !in_array($request->id, $addedServices)) {
                            $services[] = [
                                "id" => $request->id,
                                "name" => $request->name,
                                "maxQuantity" => $request->getAdditionalData()['maxQuantity'] ?? 1,
                            ];
                            $addedServices[] = $request->id;
                            $found = true;
                        }
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $officeId;
            }
        }

        if (empty($services)) {
            return [
                'services' => [],
                'error' => 'Service(s) not found for the provided officeId(s)',
                'status' => 404,
            ];
        }

        $responseContent = ['services' => $services];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following officeId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return [
            'services' => $responseContent,
            'status' => 200,
        ];
    }
}
