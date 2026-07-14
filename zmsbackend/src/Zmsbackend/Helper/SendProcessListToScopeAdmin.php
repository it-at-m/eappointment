<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class SendProcessListToScopeAdmin
{
    protected $scopeList;

    protected $datetime;

    protected $verbose = false;

    public function __construct($verbose = false, $scopeId = false)
    {
        $this->dateTime = new \DateTimeImmutable();
        if ($verbose) {
            \App::$log->info('Send process list of current day to scope admin');
            $this->verbose = true;
        }
        if ($scopeId) {
            $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($scopeId);
            $this->scopeList = (new \BO\Zmsentities\Collection\ScopeList())->addEntity($scope);
        } else {
            $this->scopeList = (new \BO\Zmsbackend\Scope\Service\Scope())->readListWithScopeAdminEmail(1);
        }
    }

    public function startProcessing($commit)
    {
        foreach ($this->scopeList as $scope) {
            if ($this->verbose) {
                \App::$log->info('Processing scope', ['scope' => (string) $scope]);
            }
            if ($commit) {
                $processList = (new \BO\Zmsbackend\Process\Service\Process())
                    ->readProcessListByScopesAndTime([$scope->getId()], $this->dateTime, 1);
                $processList = $processList
                   ->toQueueList($this->dateTime)
                   ->withStatus(array('confirmed', 'queued', 'reserved'))
                   ->withSortedArrival()
                   ->toProcessList();
                if ($processList->count() > 0) {
                    if ($this->sendListToQueue($scope, $processList) && $this->verbose) {
                        \App::$log->info('Send processList to scope admin', [
                            'email' => $scope->getContactEmail(),
                        ]);
                    }
                } else {
                    \App::$log->warning('Processlist empty for scope', ['scopeId' => $scope->id]);
                }
            }
        }
    }

    protected function sendListToQueue($scope, $processList)
    {
        $entity = (new \BO\Zmsentities\Mail())->toScopeAdminProcessList($processList, $scope, $this->dateTime);
        if (! (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueueWithDailyProcessList($scope, $entity)) {
            \App::$log->warning('Mail writing in queue not successful');
        }
    }
}
