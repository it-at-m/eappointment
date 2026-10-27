<?php

namespace BO\Zmsbackend\Helper;

use BO\Zmsbackend\Application as App;

/**
 * Rebuild every shared cache entry whose key does not depend on a search text
 * or a department set chosen on the request.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WarmSharedCache extends \BO\Zmsbackend\Base
{
    private const array RESOLVE_LEVELS = [0, 1, 2];

    public function write(): void
    {
        if (!App::$cache) {
            return;
        }

        $this->warmCatalog();
        $this->warmUseraccounts();

        if (App::$log) {
            App::$log->info('Rebuilt the shared API cache');
        }
    }

    private function warmCatalog(): void
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
            $this->warmProviders($source);
            $this->warmRequests($source);
        }

        $this->warmDepartments();
        $this->warmClusters();
        $this->warmScopes();
    }

    private function warmProviders(string $source): void
    {
        $providers = null;
        $this->safely(function () use ($source, &$providers) {
            $providers = (new \BO\Zmsbackend\Provider\Service\Provider())->readListBySource($source, 0);
        });
        if ($providers === null) {
            return;
        }

        $providerService = new \BO\Zmsbackend\Provider\Service\Provider();
        $scopeService = new \BO\Zmsbackend\Scope\Service\Scope();
        foreach ($providers as $provider) {
            $providerId = (string) $provider->getId();
            if ($providerId === '') {
                continue;
            }
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($providerService, $source, $providerId, $level) {
                    $providerService->readEntity($source, $providerId, $level, true);
                });
                $this->safely(function () use ($providerService, $providerId, $level) {
                    $providerService->readEntityById($providerId, $level, true);
                });
                $this->safely(function () use ($scopeService, $providerId, $level) {
                    $scopeService->readByProviderId($providerId, $level, true);
                });
            }
        }
    }

    private function warmRequests(string $source): void
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
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($requests, $source, $requestId, $level) {
                    $requests->readEntity($source, $requestId, $level, true);
                });
                $this->safely(function () use ($requests, $requestId, $level) {
                    $requests->readEntityById($requestId, $level, true);
                });
            }
        }
    }

    private function warmDepartments(): void
    {
        $departments = null;
        $this->safely(function () use (&$departments) {
            $departments = (new \BO\Zmsbackend\Department\Service\Department())->readList(0);
        });
        if ($departments === null) {
            return;
        }

        $departmentService = new \BO\Zmsbackend\Department\Service\Department();
        $scopeService = new \BO\Zmsbackend\Scope\Service\Scope();
        foreach ($departments as $department) {
            $departmentId = (string) $department->getId();
            if ($departmentId === '') {
                continue;
            }
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($departmentService, $departmentId, $level) {
                    $departmentService->readEntity($departmentId, $level, true);
                });
                $this->safely(function () use ($scopeService, $departmentId, $level) {
                    $scopeService->readByDepartmentId((int) $departmentId, $level, true);
                });
            }
        }
    }

    private function warmClusters(): void
    {
        $clusters = null;
        $this->safely(function () use (&$clusters) {
            $clusters = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readList(0);
        });
        if ($clusters === null) {
            return;
        }

        $clusterService = new \BO\Zmsbackend\Cluster\Service\Cluster();
        foreach ($clusters as $cluster) {
            $clusterId = (string) $cluster->getId();
            if ($clusterId === '') {
                continue;
            }
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($clusterService, $clusterId, $level) {
                    $clusterService->readEntity($clusterId, $level, true);
                });
            }
        }
    }

    private function warmScopes(): void
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
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($scopeService, $scopeId, $level) {
                    $scopeService->readEntity($scopeId, $level, true);
                });
            }
        }
    }

    private function warmUseraccounts(): void
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
            foreach (self::RESOLVE_LEVELS as $level) {
                $this->safely(function () use ($users, $login, $level) {
                    $users->readEntity($login, $level, true);
                });
            }
            $this->warmWorkstationCopy($users, $account, $roles);
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
