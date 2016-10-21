<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{
    public function getAlternateRedirectUrl()
    {
        $result = null;
        $scope = reset($this);
        $alternateUrl = $scope->toProperty()->preferences->client->alternateAppointmentUrl->get();
        if (1 == count($this) && $alternateUrl) {
            $result = $alternateUrl;
        }
        return $result;
    }
}
