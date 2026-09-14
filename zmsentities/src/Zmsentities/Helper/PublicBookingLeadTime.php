<?php

namespace BO\Zmsentities\Helper;

class PublicBookingLeadTime
{
    public const string ENV_NAME = 'ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES';

    public static function minutes(): int
    {
        $value = getenv(self::ENV_NAME);
        if ($value === false || $value === '' || !is_numeric($value)) {
            return 30;
        }

        return max(0, (int) $value);
    }

    public static function seconds(): int
    {
        return self::minutes() * 60;
    }
}
