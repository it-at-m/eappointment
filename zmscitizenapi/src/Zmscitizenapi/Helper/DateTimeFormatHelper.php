<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Helper;

class DateTimeFormatHelper
{

    private static function formatDateArray(\DateTime $date): array
    {
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }

    public static function getInternalDateFromISO(string $dateString): array
    {
        try {
            if (!is_string($dateString)) {
                throw new \InvalidArgumentException('Date string must be a string');
            }
            $date = new \DateTime($dateString);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid ISO date format: ' . $e->getMessage());
        }
    }

    public static function getInternalDateFromTimestamp(int $timestamp): array
    {
        try {
            $date = (new \DateTime())->setTimestamp($timestamp);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid timestamp: ' . $e->getMessage());
        }
    }

}
