<?php

namespace BO\Zmsbackend\Helper;

use BO\Slim\Helper\ProcessPool;
use BO\Zmsbackend\Application as App;

/**
 * Rebuild every shared cache entry whose key does not depend on a search text
 * or a department set chosen on the request.
 *
 * Lists and other single keys are written here. Each provider, request,
 * department, cluster, scope, and account is then handed to a worker. Account
 * jobs start after the catalog jobs so a department scope key already exists
 * before several accounts read it.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WarmSharedCache extends \BO\Zmsbackend\Base
{
    private const array RESOLVE_LEVELS = [0, 1, 2];

    public function write(?string $namespace = null): void
    {
        if (!App::$cache) {
            return;
        }

        $catalogJobs = [];
        $this->warmCatalog($catalogJobs);
        $this->dispatch($catalogJobs, $namespace);
        $userJobs = [];
        $this->warmUseraccounts($userJobs);
        $this->dispatch($userJobs, $namespace);

        if (App::$log) {
            App::$log->info('Rebuilt the shared API cache');
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    public function writeJobs(array $jobs): void
    {
        $users = new \BO\Zmsbackend\Useraccount\Service\Useraccount();
        $roles = null;
        foreach ($jobs as $job) {
            if (!is_array($job)) {
                continue;
            }
            $kind = isset($job['kind']) ? (string) $job['kind'] : '';
            $id = isset($job['id']) ? (string) $job['id'] : '';
            if ($kind === '' || $id === '') {
                continue;
            }
            $source = isset($job['source']) ? (string) $job['source'] : '';
            if ($kind === 'user' && $roles === null) {
                $roles = $this->roleNames();
            }
            $this->safely(function () use ($kind, $id, $source, $users, &$roles) {
                $this->writeOne($kind, $id, $source, $users, $roles ?? []);
            });
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function dispatch(array $jobs, ?string $namespace): void
    {
        $script = dirname(__DIR__, 3) . '/bin/warmSharedCacheWorker';
        $workers = min(ProcessPool::workerCount(), count($jobs));
        if ($jobs === [] || $namespace === null || $workers < 2 || !is_file($script)) {
            $this->writeJobs($jobs);
            return;
        }

        $chunks = array_chunk($jobs, (int) ceil(count($jobs) / $workers));
        $files = [];
        try {
            $commands = [];
            foreach ($chunks as $chunk) {
                $file = tempnam(sys_get_temp_dir(), 'zms-warm-');
                if ($file === false) {
                    throw new \RuntimeException('Could not create a warmup job file');
                }
                file_put_contents($file, json_encode(array_values($chunk), JSON_THROW_ON_ERROR));
                $files[] = $file;
                $commands[] = [ProcessPool::phpBinary(), $script, '--namespace=' . $namespace, '--jobs=' . $file];
            }

            if (App::$log) {
                App::$log->info('Rebuilding shared cache entries in parallel', [
                    'workers' => count($commands),
                    'jobs' => count($jobs),
                ]);
            }

            $failed = ProcessPool::run($commands, dirname($script, 2));
            foreach ($failed as $index) {
                if (App::$log) {
                    App::$log->error('A cache warmup worker failed', ['worker' => $index]);
                }
                $this->writeJobs($chunks[$index]);
            }
        } catch (\Throwable $exception) {
            $this->logFailure($exception);
            $this->writeJobs($jobs);
        } finally {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * @param array<int, string> $roles
     */
    private function writeOne(
        string $kind,
        string $id,
        string $source,
        \BO\Zmsbackend\Useraccount\Service\Useraccount $users,
        array $roles
    ): void {
        if ($kind === 'user') {
            $account = null;
            foreach (self::RESOLVE_LEVELS as $level) {
                $loaded = $users->readEntity($id, $level, true);
                if ($level === 1 && $loaded instanceof \BO\Zmsentities\Useraccount) {
                    $account = $loaded;
                }
            }
            if ($account instanceof \BO\Zmsentities\Useraccount) {
                $this->warmWorkstationCopy($users, $account, $roles);
            }
            return;
        }

        $providers = new \BO\Zmsbackend\Provider\Service\Provider();
        $scopes = new \BO\Zmsbackend\Scope\Service\Scope();
        $requests = new \BO\Zmsbackend\Request\Service\Request();
        $departments = new \BO\Zmsbackend\Department\Service\Department();
        $clusters = new \BO\Zmsbackend\Cluster\Service\Cluster();
        foreach (self::RESOLVE_LEVELS as $level) {
            if ($kind === 'provider') {
                $this->writeProvider($providers, $scopes, $source, $id, $level);
            } elseif ($kind === 'request') {
                $this->writeRequest($requests, $source, $id, $level);
            } elseif ($kind === 'department') {
                $this->writeDepartment($departments, $scopes, $id, $level);
            } elseif ($kind === 'cluster') {
                $clusters->readEntity($id, $level, true);
            } elseif ($kind === 'scope') {
                $scopes->readEntity($id, $level, true);
            }
        }
    }

    private function writeProvider(
        \BO\Zmsbackend\Provider\Service\Provider $providers,
        \BO\Zmsbackend\Scope\Service\Scope $scopes,
        string $source,
        string $id,
        int $level
    ): void {
        $providers->readEntity($source, $id, $level, true);
        $providers->readEntityById($id, $level, true);
        $scopes->readByProviderId($id, $level, true);
    }

    private function writeRequest(
        \BO\Zmsbackend\Request\Service\Request $requests,
        string $source,
        string $id,
        int $level
    ): void {
        $requests->readEntity($source, $id, $level, true);
        $requests->readEntityById($id, $level, true);
    }

    private function writeDepartment(
        \BO\Zmsbackend\Department\Service\Department $departments,
        \BO\Zmsbackend\Scope\Service\Scope $scopes,
        string $id,
        int $level
    ): void {
        $departments->readEntity($id, $level, true);
        $scopes->readByDepartmentId((int) $id, $level, true);
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmCatalog(array &$jobs): void
    {
        $this->safely(function () {
            (new \BO\Zmsbackend\Config\Service\Config())->readEntity(true);
        });
        $this->safely(function () {
            (new \BO\Zmsbackend\Dayoff\Service\DayOff())->readCommon(true);
        });

        foreach ($this->sourceNames() as $source) {
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($source, $level) {
                    (new \BO\Zmsbackend\Source\Service\Source())->readEntity($source, $level, true);
                });
            }
            $this->warmProviders($source, $jobs);
            $this->warmRequests($source, $jobs);
        }

        $this->warmDepartments($jobs);
        $this->warmClusters($jobs);
        $this->warmScopes($jobs);
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmProviders(string $source, array &$jobs): void
    {
        $providers = null;
        $this->safely(function () use ($source, &$providers) {
            $providers = (new \BO\Zmsbackend\Provider\Service\Provider())->readListBySource($source, 0);
        });
        if ($providers === null) {
            return;
        }

        foreach ($providers as $provider) {
            $providerId = (string) $provider->getId();
            if ($providerId === '') {
                continue;
            }
            $jobs[] = ['kind' => 'provider', 'id' => $providerId, 'source' => $source];
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmRequests(string $source, array &$jobs): void
    {
        $requests = new \BO\Zmsbackend\Request\Service\Request();
        $list = null;
        foreach (self::RESOLVE_LEVELS as $level) {
            $this->safely(function () use ($requests, $source, $level, &$list) {
                $loaded = $requests->readListBySource($source, $level, true);
                if ($level === 0) {
                    $list = $loaded;
                }
            });
        }
        if ($list === null) {
            return;
        }

        foreach ($list as $request) {
            $requestId = (string) $request->getId();
            if ($requestId === '') {
                continue;
            }
            $jobs[] = ['kind' => 'request', 'id' => $requestId, 'source' => $source];
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmDepartments(array &$jobs): void
    {
        $departments = null;
        $this->safely(function () use (&$departments) {
            $departments = (new \BO\Zmsbackend\Department\Service\Department())->readList(0);
        });
        if ($departments === null) {
            return;
        }

        foreach ($departments as $department) {
            $departmentId = (string) $department->getId();
            if ($departmentId === '') {
                continue;
            }
            $jobs[] = ['kind' => 'department', 'id' => $departmentId];
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmClusters(array &$jobs): void
    {
        $clusters = null;
        $this->safely(function () use (&$clusters) {
            $clusters = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readList(0);
        });
        if ($clusters === null) {
            return;
        }

        foreach ($clusters as $cluster) {
            $clusterId = (string) $cluster->getId();
            if ($clusterId === '') {
                continue;
            }
            $jobs[] = ['kind' => 'cluster', 'id' => $clusterId];
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmScopes(array &$jobs): void
    {
        $scopeService = new \BO\Zmsbackend\Scope\Service\Scope();
        $scopes = null;
        foreach (self::RESOLVE_LEVELS as $level) {
            $this->safely(function () use ($scopeService, $level, &$scopes) {
                $loaded = $scopeService->readList($level, true);
                if ($level === 0) {
                    $scopes = $loaded;
                }
            });
        }
        if ($scopes === null) {
            return;
        }

        foreach ($scopes as $scope) {
            $scopeId = $scope->getId();
            if ($scopeId === null || $scopeId === '') {
                continue;
            }
            $jobs[] = ['kind' => 'scope', 'id' => (string) $scopeId];
        }
    }

    /**
     * @param list<array{kind: string, id: string, source?: string}> $jobs
     */
    private function warmUseraccounts(array &$jobs): void
    {
        $users = new \BO\Zmsbackend\Useraccount\Service\Useraccount();
        $roles = $this->roleNames();
        foreach (self::RESOLVE_LEVELS as $level) {
            $this->safely(function () use ($users, $level) {
                $users->readList($level, true);
            });
            foreach ($roles as $roleName) {
                $this->safely(function () use ($users, $roleName, $level) {
                    $users->readListRole($roleName, $level, null, true);
                });
            }
        }
        $this->safely(function () use ($users) {
            $users->readSearch([], 0, null, true);
        });

        $accounts = null;
        $this->safely(function () use ($users, &$accounts) {
            $accounts = $users->readList(1);
        });
        if ($accounts === null) {
            return;
        }

        foreach ($accounts as $account) {
            if (!$account instanceof \BO\Zmsentities\Useraccount) {
                continue;
            }
            $login = (string) $account->getId();
            if ($login === '') {
                continue;
            }
            $jobs[] = ['kind' => 'user', 'id' => $login];
        }
    }

    /**
     * @param array<int, string> $roles
     */
    private function warmWorkstationCopy(
        \BO\Zmsbackend\Useraccount\Service\Useraccount $users,
        \BO\Zmsentities\Useraccount $account,
        array $roles
    ): void {
        if ($account->isSuperUser() || $account->getDepartmentList()->count() === 0) {
            return;
        }

        $workstation = new \BO\Zmsentities\Workstation();
        $workstation->useraccount = $account;
        $this->safely(function () use ($users, $workstation) {
            $users->readList(0, true, $workstation);
        });
        $this->safely(function () use ($users, $workstation) {
            $users->readSearch([], 0, $workstation, true);
        });
        foreach ($roles as $roleName) {
            $this->safely(function () use ($users, $roleName, $workstation) {
                $users->readListRole($roleName, 0, $workstation, true);
            });
        }
    }

    /**
     * @return array<int, string>
     */
    private function sourceNames(): array
    {
        $names = [];
        try {
            $sources = (new \BO\Zmsbackend\Source\Service\Source())->readList(0);
        } catch (\Throwable $exception) {
            $this->logFailure($exception);
            return [];
        }

        foreach ($sources as $source) {
            $name = (string) $source->getSource();
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @return array<int, string>
     */
    private function roleNames(): array
    {
        try {
            $rows = $this->fetchAll('SELECT name FROM role ORDER BY name');
        } catch (\Throwable $exception) {
            $this->logFailure($exception);
            return [];
        }

        $names = [];
        foreach ($rows as $row) {
            $name = isset($row['name']) ? (string) $row['name'] : '';
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    private function safely(callable $read): void
    {
        try {
            $read();
        } catch (\Throwable $exception) {
            $this->logFailure($exception);
        }
    }

    private function logFailure(\Throwable $exception): void
    {
        if (App::$log) {
            App::$log->error('Failed to rebuild one shared cache entry', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
