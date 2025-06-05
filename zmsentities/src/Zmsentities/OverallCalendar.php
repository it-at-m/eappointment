<?php

namespace BO\Zmsentities;

class OverallCalendar extends Schema\Entity
{
    public const PRIMARY = 'days';

    public static $schema = "overallCalendar.json";

    public function getDefaults()
    {
        return [
            'data' => [
                'days' => []
            ],
            'meta' => []
        ];
    }

    public function getDays()
    {
        return $this->data['days'] ?? [];
    }

    public function setDays(array $days)
    {
        $this->data['days'] = $days;
        return $this;
    }
} 