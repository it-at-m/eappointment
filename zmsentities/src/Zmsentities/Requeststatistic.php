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

    public function getScopeRequests(): Collection\RequestList
    {
        $value = $this['scopeRequests'] ?? null;
        return $value instanceof Collection\RequestList ? $value : new Collection\RequestList();
    }

    public function getAdditionalDepartmentRequests(): Collection\RequestList
    {
        $value = $this['additionalDepartmentRequests'] ?? null;
        return $value instanceof Collection\RequestList ? $value : new Collection\RequestList();
    }

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'scopeRequests' => new Collection\RequestList(),
            'additionalDepartmentRequests' => new Collection\RequestList(),
        ];
    }

    /**
     * @param array|object $mergeData
     */
    #[\Override]
    public function addData($mergeData): static
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
        $scopeRequests = array_values($this->getScopeRequests()->getArrayCopy());
        $additionalDepartmentRequests = array_values($this->getAdditionalDepartmentRequests()->getArrayCopy());

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
