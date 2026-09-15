<?php

namespace BO\Zmsentities\Helper;

use BO\Zmsentities\Scope;

class PublicBookingLeadTime
{
    public const string ENV_NAME = 'ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES';

    public static function minutes(?Scope $scope = null): int
    {
        if ($scope !== null) {
            $activationDuration = $scope->getPreference('appointment', 'activationDuration');
            if ($activationDuration !== null && $activationDuration !== '' && is_numeric($activationDuration)) {
                return max(0, (int) $activationDuration);
            }
        }

        $value = getenv(self::ENV_NAME);
        if ($value === false || $value === '' || !is_numeric($value)) {
            return 30;
        }

        return max(0, (int) $value);
    }

    public static function seconds(?Scope $scope = null): int
    {
        return self::minutes($scope) * 60;
    }
}
