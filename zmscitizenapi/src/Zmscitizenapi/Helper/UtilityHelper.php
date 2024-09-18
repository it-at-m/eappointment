<?php

namespace BO\Zmscitizenapi\Helper;

class UtilityHelper
{
    public function getInternalDateFromISO($dateString)
    {
        $date = new \DateTime($dateString);
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }

    public function getInternalDateFromTimestamp(int $timestamp)
    {
        $date = (new \DateTime())->setTimestamp($timestamp);
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y')
        ];
    }

    public function uniqueElementsFilter($value, $index, $self)
    {
        return array_search($value, $self) === $index;
    }
}
