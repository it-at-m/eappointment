<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class CalculateSlots
{
    private const int MAX_WORKERS = 16;

    private const string CALCULATION_LOCK = 'zms_calculate_slots';

    protected $verbose = false;

    protected $startTime;

    protected $logList = [];

    /** @var list<int> */
    private array $calculatedScopeIds = [];

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
    }

    public function log(
        string $message,
        ?int $scopeId = null,
        ?int $availabilityId = null,
        ?float $durationSeconds = null
    ): static {
        $time = $this->getSpendTime();
        $memory = memory_get_usage() / (1024 * 1024);
        $text = sprintf("[CalculateSlots %07.3fs %07.1fmb] %s", "$time", $memory, $message);
        $context = [
            'component' => 'CalculateSlots',
            'elapsed' => $time,
            'memory_mb' => $memory,
            'scope_id' => $scopeId,
            'availability_id' => $availabilityId,
        ];
        if ($durationSeconds !== null) {
            $context['duration_seconds'] = $durationSeconds;
        }
        $emit = $this->verbose || $availabilityId !== null;
        if ($emit) {
            \App::$log->info($message, $context);
        }
        $this->logList[] = [
            'text' => $text,
            'scope_id' => $scopeId,
            'availability_id' => $availabilityId,
            'emitted' => $emit,
        ];
        return $this;
    }

    public function dumpLogs(): void
    {
        foreach ($this->logList as $entry) {
            if ($entry['emitted']) {
                continue;
            }
            \App::$log->info($entry['text'], [
                'component' => 'CalculateSlots',
                'scope_id' => $entry['scope_id'],
                'availability_id' => $entry['availability_id'],
            ]);
        }
        $this->verbose = true;
    }

    /**
     * @return list<int>
     */
    public function calculatedScopeIds(): array
    {
        return $this->calculatedScopeIds;
    }

    public function getSpendTime(): float
    {
        $time = round(microtime(true) - $this->startTime, 3);
        return $time;
    }

    protected function readCalculateSkip()
    {
        $skip = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsSkip');
        return $skip;
    }

    protected function readForceVerbose()
    {
        $force = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsForceVerbose');
        if ($force) {
            $this->log("Forced verbose, see table config.status__calculateSlotsForceVerbose");
            $this->dumpLogs();
        }
        return $force;
    }

    protected function readLastRun()
    {
        $updateTimestamp = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsLastRun', true);
        return $updateTimestamp;
    }

    public function writeMaintenanceQueries(bool $daily = false)
    {
        $sqlList = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsMaintenanceSQL');
        if ($sqlList) {
            $pdo = \BO\Zmsbackend\Connection\Select::getWriteConnection();
            foreach (explode("\n", $sqlList) as $sql) {
                $this->log("Maintenance query: $sql");
                $pdo->exec($sql);
            }
            return \BO\Zmsbackend\Connection\Select::writeCommit();
        }

        if ($daily) {
            (new \BO\Zmsbackend\Slot\Service\Slot())->writeOptimizedSlotTables();
            $this->log("Optimized tables successfully");
        }
    }


    public function writeCalculations(\DateTimeInterface $now, bool $daily = false): bool
    {
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $this->readForceVerbose();
        $this->log("Calculate with time " . $now->format('c'));
        if ($this->readCalculateSkip()) {
            $this->log("Skip calculation due to config setting status.calculateSlotsSkip");
            return false;
        }
        $startTimestamp = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsLastStart', true);
        $updateTimestamp = (new \BO\Zmsbackend\Config\Service\Config())->readProperty('status__calculateSlotsLastRun', false);
        if ($startTimestamp > $updateTimestamp && ((strtotime($startTimestamp) + (60 * 15)) > $now->getTimestamp())) {
            $this->log("Skip calculation, last start on $startTimestamp is not yet finished, waiting 15 minutes.");
            return false;
        }
        if (!$this->acquireCalculationLock()) {
            $this->log("Skip calculation, another run is still working.");
            return false;
        }
        try {
            return $this->writeCalculationsLocked($now, $daily);
        } finally {
            $this->releaseCalculationLock();
        }
    }

    /**
     * @param list<int> $scopeIds
     */
    public function writeScopeIds(\DateTimeInterface $now, array $scopeIds): void
    {
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $scopeService = new \BO\Zmsbackend\Scope\Service\Scope();
        $total = count($scopeIds);
        foreach ($scopeIds as $key => $scopeId) {
            $scope = $scopeService->readEntity($scopeId, 1);
            if ($scope === null) {
                $this->log("Skip missing scope $scopeId", $scopeId);
                continue;
            }
            if ($this->writeCalculatedScope($scope, $now)) {
                $this->log("Calculated slots $key/" . ($total - 1) . " for $scope", (int) $scope->id);
            }
        }
    }

    private function writeCalculationsLocked(\DateTimeInterface $now, bool $daily): bool
    {
        (new \BO\Zmsbackend\Config\Service\Config())->replaceProperty('status__calculateSlotsLastStart', $now->format('Y-m-d H:i:s'));
        \BO\Zmsbackend\Connection\Select::writeCommit();
        $updateTimestamp = $this->readLastRun();
        $this->log("Last Run on time=" . $updateTimestamp);
        if ($daily) {
            $this->deleteOldSlots($now);
        }
        \BO\Zmsbackend\Connection\Select::writeCommit();
        $scopeList = (new \BO\Zmsbackend\Scope\Service\Scope())->readList(1);
        \BO\Zmsbackend\Connection\Select::writeCommit();
        $this->writeScopesInParallel($scopeList, $now);

        $this->writeCanceledSlots($now);

        $slotQuery = new \BO\Zmsbackend\Slot\Service\Slot();
        if ($slotsProcessed = $slotQuery->deleteSlotProcessOnProcess()) {
            $this->log("Finished to free $slotsProcessed slots for changed/deleted processes");
        }

        $slotQuery->deleteSlotProcessOnSlot();

        if ($slotsProcessed = $slotQuery->updateSlotProcessMapping()) {
            $this->log("Updated Slot-Process-Mapping, mapped $slotsProcessed processes");
        }

        (new \BO\Zmsbackend\Config\Service\Config())->replaceProperty('status__calculateSlotsLastRun', $now->format('Y-m-d H:i:s'));

        \BO\Zmsbackend\Connection\Select::writeCommit();
        $this->log("Slot calculation finished");
        $this->writeMaintenanceQueries($daily);

        return true;
    }

    protected function writeCalculatedScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now): bool
    {
        $slotQuery = new \BO\Zmsbackend\Slot\Service\Slot();
        $updatedList = $slotQuery->writeByScope($scope, $now);
        if (count($updatedList)) {
            $this->writePostProcessingByScope($scope, $now);
            \BO\Zmsbackend\Connection\Select::writeCommitWithStartLock();
            $this->readLastRun();
            \BO\Zmsbackend\Connection\Select::writeCommit();
            return true;
        }
        return false;
    }

    private function writeScopesInParallel(iterable $scopeList, \DateTimeInterface $now): void
    {
        $scopeIds = [];
        foreach ($scopeList as $scope) {
            $scopeId = (int) $scope->id;
            if ($scopeId > 0) {
                $scopeIds[] = $scopeId;
            }
        }
        $this->calculatedScopeIds = $scopeIds;
        $workerCount = min(self::MAX_WORKERS, count($scopeIds));
        if ($workerCount <= 1) {
            $this->writeScopeIds($now, $scopeIds);
            return;
        }

        $chunks = array_fill(0, $workerCount, []);
        foreach ($scopeIds as $index => $scopeId) {
            $chunks[$index % $workerCount][] = $scopeId;
        }
        $this->log("Calculate " . count($scopeIds) . " scopes on $workerCount workers");
        foreach ($this->runScopeWorkers($chunks, $now) as $chunk) {
            $this->log("Retry scope chunk in this process: " . implode(',', $chunk));
            $this->writeScopeIds($now, $chunk);
        }
    }

    /**
     * @param list<list<int>> $chunks
     * @return list<list<int>>
     */
    private function runScopeWorkers(array $chunks, \DateTimeInterface $now): array
    {
        $running = [];
        $failed = [];
        foreach ($chunks as $index => $chunk) {
            $worker = $this->openScopeWorker($chunk, $now);
            if ($worker === null) {
                $failed[] = $chunk;
                continue;
            }
            $running[$index] = $worker;
        }

        while ($running !== []) {
            $this->waitForScopeWorkers($running);
            foreach ($running as $index => $worker) {
                $this->forwardWorker($worker['stdout'], 'stdout');
                $this->forwardWorker($worker['stderr'], 'stderr');
                $status = proc_get_status($worker['process']);
                if ($status['running']) {
                    continue;
                }
                $this->forwardWorker($worker['stdout'], 'stdout');
                $this->forwardWorker($worker['stderr'], 'stderr');
                fclose($worker['stdout']);
                fclose($worker['stderr']);
                proc_close($worker['process']);
                if ($status['exitcode'] !== 0) {
                    $failed[] = $chunks[$index];
                }
                unset($running[$index]);
            }
        }

        return $failed;
    }

    /**
     * @param list<int> $scopeIds
     * @return array{process: resource, stdout: resource, stderr: resource}|null
     */
    private function openScopeWorker(array $scopeIds, \DateTimeInterface $now): ?array
    {
        $command = [
            $this->phpBinary(),
            dirname(__DIR__, 3) . '/bin/calculateSlots',
            '--commit',
            '--scopes=' . implode(',', $scopeIds),
            $now->format('c'),
        ];
        if ($this->verbose) {
            $command[] = '--verbose';
        }
        $pipes = [];
        $process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes, dirname(__DIR__, 3));
        if (!is_resource($process)) {
            return null;
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        return [
            'process' => $process,
            'stdout' => $pipes[1],
            'stderr' => $pipes[2],
        ];
    }

    /**
     * @param array<int, array{process: resource, stdout: resource, stderr: resource}> $running
     */
    private function waitForScopeWorkers(array $running): void
    {
        $read = [];
        foreach ($running as $worker) {
            if (is_resource($worker['stdout'])) {
                $read[] = $worker['stdout'];
            }
            if (is_resource($worker['stderr'])) {
                $read[] = $worker['stderr'];
            }
        }
        if ($read === []) {
            return;
        }
        $write = null;
        $except = null;
        @stream_select($read, $write, $except, 1);
    }

    /**
     * @param resource $from
     */
    private function forwardWorker($from, string $which): void
    {
        if (!is_resource($from)) {
            return;
        }
        $chunk = stream_get_contents($from);
        if (!is_string($chunk) || $chunk === '') {
            return;
        }
        if ($which === 'stderr' && defined('STDERR')) {
            fwrite(STDERR, $chunk);
            return;
        }
        if (defined('STDOUT')) {
            fwrite(STDOUT, $chunk);
        }
    }

    private function phpBinary(): string
    {
        $binary = PHP_BINARY;
        $base = strtolower(basename($binary));
        if ($binary !== '' && is_executable($binary) && !str_contains($base, 'fpm') && !str_contains($base, 'httpd')) {
            return $binary;
        }

        return 'php';
    }

    private function acquireCalculationLock(): bool
    {
        $locked = \BO\Zmsbackend\Connection\Select::getWriteConnection()->fetchValue(
            'SELECT GET_LOCK(:name, 0)',
            ['name' => self::CALCULATION_LOCK]
        );

        return (int) $locked === 1;
    }

    private function releaseCalculationLock(): void
    {
        \BO\Zmsbackend\Connection\Select::getWriteConnection()->fetchValue(
            'SELECT RELEASE_LOCK(:name)',
            ['name' => self::CALCULATION_LOCK]
        );
    }

    public function writePostProcessingByScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now): void
    {
        $slotQuery = new \BO\Zmsbackend\Slot\Service\Slot();
        if ($slotsProcessed = $slotQuery->deleteSlotProcessOnProcess($scope->id)) {
            $this->log("Finished to free $slotsProcessed slots for changed/deleted processes", (int) $scope->id);
        }
        $slotQuery->writeCanceledByTimeAndScope($now, $scope);
        $slotQuery->deleteSlotProcessOnSlot($scope->id);
        //$this->log("Finished to free slots for cancelled availabilities");

        if ($slotsProcessed = $slotQuery->updateSlotProcessMapping($scope->id)) {
            $this->log("Updated Slot-Process-Mapping, mapped $slotsProcessed processes", (int) $scope->id);
        }
    }

    public function writeCanceledSlots(\DateTimeInterface $now, $modify = '+5 minutes'): bool
    {
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $slotQuery = new \BO\Zmsbackend\Slot\Service\Slot();
        if ($slotQuery->writeCanceledByTime($now->modify($modify))) {
            \BO\Zmsbackend\Connection\Select::writeCommit();
            $this->log("Cancelled slots older than " . $now->modify($modify)->format('c'));
            return true;
        }
        return false;
    }

    public function deleteOldSlots(\DateTimeInterface $now): void
    {
        $this->log("Maintenance: Delete slots older than " . $now->format('Y-m-d'));
        $slotQuery = new \BO\Zmsbackend\Slot\Service\Slot();
        $pdo = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $pdo->exec('SET SESSION innodb_lock_wait_timeout=600');
        if ($slotQuery->deleteSlotsOlderThan($now)) {
            \BO\Zmsbackend\Connection\Select::writeCommit();
            $this->log("Deleted old slots successfully");
        }
    }
}
