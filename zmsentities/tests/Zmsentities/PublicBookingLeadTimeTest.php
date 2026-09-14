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
}
