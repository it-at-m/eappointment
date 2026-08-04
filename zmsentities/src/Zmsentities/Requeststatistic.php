<?php

declare(strict_types=1);

namespace BO\Zmsentities;

/**
 * Grouped requests for statistic collection after process finish (ZMSKVR-1431).
 *
 * @property Collection\RequestList $scope
 * @property Collection\RequestList $additional
 */
class Requeststatistic extends Schema\Entity implements Helper\NoSanitize
{
    public static $schema = 'requeststatistic.json';

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'scope' => new Collection\RequestList(),
            'additional' => new Collection\RequestList(),
        ];
    }

    #[\Override]
    public function addData($mergeData): Schema\Entity
    {
        if (isset($mergeData['scope']) && !$mergeData['scope'] instanceof Collection\RequestList) {
            $mergeData['scope'] = (new Collection\RequestList())->addData($mergeData['scope']);
        }
        if (isset($mergeData['additional']) && !$mergeData['additional'] instanceof Collection\RequestList) {
            $mergeData['additional'] = (new Collection\RequestList())->addData($mergeData['additional']);
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
        $additional = $this->additional instanceof Collection\RequestList
            ? array_values($this->additional->getArrayCopy())
            : [];

        if (is_array($serialized)) {
            $serialized['scope'] = $scope;
            $serialized['additional'] = $additional;
            return $serialized;
        }

        $serialized->scope = $scope;
        $serialized->additional = $additional;
        return $serialized;
    }
}
