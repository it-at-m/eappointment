<?php

namespace BO\Zmscitizenapi\Services;

class ProcessService
{
    protected $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getProcessById($processId, $authKey)
    {
        $resolveReferences = 2;
        $process = $this->httpClient->readGetResult("/process/{$processId}/{$authKey}/", [
            'resolveReferences' => $resolveReferences
        ])->getEntity();

        return $process;
    }

    public function reserveTimeslot($appointmentProcess, $serviceIds, $serviceCounts)
    {
        $requests = [];

        foreach ($serviceIds as $index => $serviceId) {
            $count = intval($serviceCounts[$index]);
            for ($i = 0; $i < $count; $i++) {
                $requests[] = [
                    'id' => $serviceId,
                    'source' => 'dldb'
                ];
            }
        }

        $appointmentProcess['requests'] = $requests;

        return $this->httpClient->readPostResult('/process/status/reserved/', $appointmentProcess)->getEntity();
    }

    public function submitClientData($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function preconfirmProcess($process)
    {
        $url = '/process/status/preconfirmed/';
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function confirmProcess($process)
    {
        $url = '/process/status/confirmed/';
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function cancelAppointment($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/";
        return $this->httpClient->readDeleteResult($url, $process)->getEntity();
    }

    public function sendConfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/confirmation/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function sendPreconfirmationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/preconfirmation/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function sendCancelationEmail($process)
    {
        $url = "/process/{$process['id']}/{$process['authKey']}/delete/mail/";
        return $this->httpClient->readPostResult($url, $process)->getEntity();
    }

    public function getFreeDays($providers, $requests, $firstDay, $lastDay)
    {
        $requestUrl = '/calendar/';
        $dataPayload = [
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'providers' => $providers,
            'requests' => $requests,
        ];

        return $this->httpClient->readPostResult($requestUrl, $dataPayload)->getEntity();
    }

    public function getFreeTimeslots($providers, $requests, $firstDay, $lastDay)
    {
        $requestUrl = '/process/status/free/';
        $dataPayload = [
            'firstDay' => $firstDay,
            'lastDay' => $lastDay,
            'providers' => $providers,
            'requests' => $requests,
        ];
    
       // error_log(json_encode($dataPayload));
    
        // Fetch the response from the httpClient
        $result = $this->httpClient->readPostResult($requestUrl, $dataPayload);
    
        // Use a method to get the response (replace getResponse() with the actual method if different)
        $psr7Response = $result->getResponse();
    
        // Fetch the body from the PSR-7 response as a stream, and convert it to a string
        $responseBody = (string) $psr7Response->getBody();
    
        // Log the full response body for debugging
        //error_log("Full response body: " . $responseBody);
    
        // Convert the body to an associative array if it's JSON
        $decodedBody = json_decode($responseBody, true);
    
        // Return the decoded response body
        return $decodedBody;
    }
    
    
    
    
    
    
}
