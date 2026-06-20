<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Request;

class ProcessFinishedHelper extends Process
{
    public function __construct($processData, $input, $requestList, $source)
    {
        parent::__construct($processData);
        $this->getFirstClient()->addData($input['process']['clients'][0]);
        $this->setRequestData($input, $requestList, $source);
        $this->setClientsCount($input['statistic']['clientsCount']);
    }

    public function setRequestData(
        array $input,
        RequestList $requestList,
        $source
    ) {
        if (array_key_exists('ignoreRequests', $input) && $input['ignoreRequests']) {
            $this->requests = new RequestList();
            $request = new Request([
                'id' => -1,
                'source' => $source,
                'name' =>  "Ohne Erfassung",
            ]);
            $this->requests[] = $request;
        } elseif (array_key_exists('noRequestsPerformed', $input) && $input['noRequestsPerformed']) {
            $this->requests = new RequestList();
            $request = new Request([
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
