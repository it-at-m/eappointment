<?php

declare(strict_types=1);

namespace BO\Zmsentities;

/**
 * @property Collection\RequestList $scope
 * @property Collection\RequestList $outsideScope
 */
class Requeststatistic extends Schema\Entity implements Helper\NoSanitize
{
    public static $schema = 'requeststatistic.json';

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'scope' => new Collection\RequestList(),
            'outsideScope' => new Collection\RequestList(),
        ];
    }

    #[\Override]
    public function addData($mergeData): Schema\Entity
    {
        if (isset($mergeData['scope']) && !$mergeData['scope'] instanceof Collection\RequestList) {
            $mergeData['scope'] = (new Collection\RequestList())->addData($mergeData['scope']);
        }
        if (isset($mergeData['outsideScope']) && !$mergeData['outsideScope'] instanceof Collection\RequestList) {
            $mergeData['outsideScope'] = (new Collection\RequestList())->addData($mergeData['outsideScope']);
        }

        return parent::addData($mergeData);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        $serialized = parent::jsonSerialize();
        $scope = $this->scope instanceof Collection\RequestList
            ? array_values($this->scope->getArrayCopy())
            : [];
        $outsideScope = $this->outsideScope instanceof Collection\RequestList
            ? array_values($this->outsideScope->getArrayCopy())
            : [];

        if (is_array($serialized)) {
            $serialized['scope'] = $scope;
            $serialized['outsideScope'] = $outsideScope;
            return $serialized;
        }

        $serialized->scope = $scope;
        $serialized->outsideScope = $outsideScope;
        return $serialized;
    }
}
