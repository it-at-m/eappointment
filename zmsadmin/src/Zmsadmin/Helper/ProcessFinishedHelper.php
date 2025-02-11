<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

class ProcessFinishedHelper extends \BO\Zmsentities\Process
{
    public function __construct($processData, $input, $requestList, $source)
    {
        parent::__construct($processData);
        $this->getFirstClient()->addData($input['process']['clients'][0]);
        $this->setPickupData($input);
        $this->setRequestData($input, $requestList, $source);
        $this->setClientsCount($input['statistic']['clientsCount']);
    }

    public function setPickupData(array $input)
    {
        if (array_key_exists('pickupScope', $input) && 0 != $input['pickupScope']) {
            $this->status = 'pending';
            $this->scope['id'] = $input['pickupScope'];
            $this->clients[0]['emailSendCount'] = "-1";
            $this->clients[0]['notificationsSendCount'] = "-1";
            $this->queue->callCount = 0;
        }
        return $this;
    }

    public function setRequestData(
        array $input,
        \BO\Zmsentities\Collection\RequestList $requestList,
        $source
    ) {
        if (array_key_exists('ignoreRequests', $input) && $input['ignoreRequests']) {
            $this->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => -1,
                'source' => $source,
                'name' =>  "Ohne Erfassung",
            ]);
            $this->requests[] = $request;
        } elseif (array_key_exists('noRequestsPerformed', $input) && $input['noRequestsPerformed']) {
            $this->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => 0,
                'source' => $source,
                'name' =>  "Dienstleistung konnte nicht erbracht werden",
            ]);
            $this->requests[] = $request;
        } elseif (array_key_exists('requestCountList', $input)) {
            $this->requests = $requestList->withCountList($input['requestCountList']);
        }
        return $this;
    }
}
