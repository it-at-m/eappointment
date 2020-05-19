<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

class ProcessFinishedHelper extends \BO\Zmsentities\Process
{
    public function setClientData(array $input, \BO\Zmsentities\Client $firstClient)
    {
        if (array_key_exists('clients', $input['process']) && count($input['process']['clients']) > 0) {
            $firstClient->addData($input['process']['clients'][0]);
            $this->clients[0] = $firstClient;
        }
        return $this;
    }

    public function setPickupData(array $input)
    {
        if (array_key_exists('pickupScope', $input) && 0 != $input['pickupScope']) {
            $this->status = 'pending';
            $this->scope['id'] = $input['pickupScope'];
            $this->clients[0]['emailSendCount'] = 0;
            $this->clients[0]['notificationsSendCount'] = 0;
            $this->queue->callCount = 0;
        }
        return $this;
    }

    public function setRequestData(
        array $input,
        \BO\Zmsentities\Collection\RequestList $requestList,
        \BO\Zmsentities\Workstation $workstation
    ) {
        if (array_key_exists('ignoreRequests', $input) && $input['ignoreRequests']) {
            $this->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => -1,
                'source' => $workstation->getScope()->getSource(),
                'name' =>  "Ohne Erfassung",
            ]);
            $this->requests[] = $request;
        } elseif (array_key_exists('noRequestsPerformed', $input) && $input['noRequestsPerformed']) {
            $this->requests = new \BO\Zmsentities\Collection\RequestList();
            $request = new \BO\Zmsentities\Request([
                'id' => 0,
                'source' => $workstation->getScope()->getSource(),
                'name' =>  "Dienstleistung konnte nicht erbracht werden",
            ]);
            $this->requests[] = $request;
        } elseif (array_key_exists('requestCountList', $input)) {
            $this->requests = $requestList->withCountList($input['requestCountList']);
        }
        return $this;
    }
}
