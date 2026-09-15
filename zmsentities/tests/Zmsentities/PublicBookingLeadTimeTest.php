<?php

namespace BO\Zmsentities\Tests;

use BO\Zmsentities\Helper\PublicBookingLeadTime;

class PublicBookingLeadTimeTest extends Base
{
    public function testMinutesReadsEnvironmentAndFallsBackToThirty()
    {
        $previous = getenv(PublicBookingLeadTime::ENV_NAME);

        try {
            putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES=15');
            $this->assertEquals(15, PublicBookingLeadTime::minutes());
            $this->assertEquals(900, PublicBookingLeadTime::seconds());

            putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES');
            $this->assertEquals(30, PublicBookingLeadTime::minutes());
            $this->assertEquals(1800, PublicBookingLeadTime::seconds());
        } finally {
            if ($previous === false) {
                putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES');
            } else {
                putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES=' . $previous);
            }
        }
    }

    public function testMinutesPrefersScopeActivationDurationOverEnvironment()
    {
        $previous = getenv(PublicBookingLeadTime::ENV_NAME);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $scope->preferences['appointment']['activationDuration'] = '45';

        try {
            putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES=15');
            $this->assertEquals(45, PublicBookingLeadTime::minutes($scope));
            $this->assertEquals(2700, PublicBookingLeadTime::seconds($scope));

            unset($scope->preferences['appointment']['activationDuration']);
            $this->assertEquals(15, PublicBookingLeadTime::minutes($scope));
        } finally {
            if ($previous === false) {
                putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES');
            } else {
                putenv('ZMS_PUBLIC_BOOKING_LEAD_TIME_MINUTES=' . $previous);
            }
        }
    }
}
