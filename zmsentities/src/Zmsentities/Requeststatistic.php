<?php

declare(strict_types=1);

namespace BO\Zmsentities;

/**
 * @property Collection\RequestList $scopeRequests
 * @property Collection\RequestList $additionalDepartmentRequests
 */
class Requeststatistic extends Schema\Entity implements Helper\NoSanitize
{
    public static $schema = 'requeststatistic.json';

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'scopeRequests' => new Collection\RequestList(),
            'additionalDepartmentRequests' => new Collection\RequestList(),
        ];
    }

    #[\Override]
    public function addData($mergeData): Schema\Entity
    {
        if (isset($mergeData['scopeRequests']) && !$mergeData['scopeRequests'] instanceof Collection\RequestList) {
            $mergeData['scopeRequests'] = (new Collection\RequestList())->addData($mergeData['scopeRequests']);
        }
        if (
            isset($mergeData['additionalDepartmentRequests'])
            && !$mergeData['additionalDepartmentRequests'] instanceof Collection\RequestList
        ) {
            $mergeData['additionalDepartmentRequests'] = (new Collection\RequestList())
                ->addData($mergeData['additionalDepartmentRequests']);
        }

        return parent::addData($mergeData);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        $serialized = parent::jsonSerialize();
        $scopeRequests = $this->scopeRequests instanceof Collection\RequestList
            ? array_values($this->scopeRequests->getArrayCopy())
            : [];
        $additionalDepartmentRequests = $this->additionalDepartmentRequests instanceof Collection\RequestList
            ? array_values($this->additionalDepartmentRequests->getArrayCopy())
            : [];

        if (is_array($serialized)) {
            $serialized['scopeRequests'] = $scopeRequests;
            $serialized['additionalDepartmentRequests'] = $additionalDepartmentRequests;
            return $serialized;
        }

        $serialized->scopeRequests = $scopeRequests;
        $serialized->additionalDepartmentRequests = $additionalDepartmentRequests;
        return $serialized;
    }
}
