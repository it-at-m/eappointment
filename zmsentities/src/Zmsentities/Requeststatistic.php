<?php

declare(strict_types=1);

namespace BO\Zmsentities;

/**
 * Grouped requests for statistic collection after process finish (ZMSKVR-1431).
 *
 * @property Collection\RequestList $scope
 * @property Collection\RequestList $additional
 */
class Requeststatistic extends Schema\Entity
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
}
