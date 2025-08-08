<?php

namespace BO\Zmsdb\Source;

/**
 * @codeCoverageIgnore
 */
class Zmsdldb extends \BO\Zmsdb\Base
{
    public static $importPath = '';
    public static $repository = null;
    public static $verbose = false;

    public static function getFixturesImportPath()
    {
        $dir = dirname(__FILE__);
        $importPath = realpath($dir . '/../../../tests/Zmsdb/fixtures/');
        return $importPath;
    }

    public static function setImportPath($path)
    {
        self::$importPath = $path;
    }

    public function startImport($verbose = true, $updateAvailability = true)
    {
        if (!static::$importPath) {
            throw new \Exception('No data path given');
        }
        if ($verbose) {
            self::$verbose = $verbose;
            print("Use source-path for dldb: " . static::$importPath . "\n\n");
        }
        self::$repository = new \BO\Zmsdldb\FileAccess();
        self::$repository->loadFromPath(static::$importPath);

        \BO\Zmsdb\Connection\Select::setTransaction();
        $this->writeRequestList();
        $providers = $this->writeProviderList();
        $this->writeRequestRelationList();
        $this->writeLastUpdate($verbose);
        \BO\Zmsdb\Connection\Select::writeCommit();

        if ($updateAvailability) {
            $this->updateAvailability($providers);
        }
    }

    protected function writeRequestList()
    {
        $startTime = microtime(true);
        $requestQuery = (new \BO\Zmsdb\Request());
        $requestQuery->writeDeleteListBySource('dldb');
        foreach (self::$repository->fromService()->fetchList() as $request) {
            if (isset($request['relation']) && isset($request['relation']['root_topic'])) {
                $topic = self::$repository->fromTopic()->fetchId($request['relation']['root_topic']);
                if ($topic && isset($topic['name'])) {
                    $request['group'] = $topic['name'];
                }
            }
            $requestQuery->writeImportEntity($request);
        }
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            print("Requests: Took $time seconds\n\n");
        }
    }

    protected function writeProviderList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Provider())->writeDeleteListBySource('dldb');
        $providers = (new \BO\Zmsdb\Provider())->writeImportList(self::$repository->fromLocation()->fetchList());

        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            print("Provider: Took $time seconds\n\n");
        }

        return $providers;
    }

    protected function updateAvailability($providers)
    {
        foreach ($providers as $provider) {
            $providerData = $provider->data;

            $scopes = (new \BO\Zmsdb\Scope())->readByProviderId($provider->getId());
            foreach ($scopes as $scope) {
                $availabilities = (new \BO\Zmsdb\Availability())->readList($scope->getId());

                foreach ($availabilities as $availability) {
                    if ((int) $availability->slotTimeInMinutes === (int) $providerData['slotTimeInMinutes']) {
                        continue;
                    }

                    $availability->slotTimeInMinutes = $providerData['slotTimeInMinutes'];
                    (new \BO\Zmsdb\Availability())
                        ->updateEntity($availability->getId(), $availability, 2);
                }
            }
        }
    }

    protected function writeRequestRelationList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\RequestRelation())->writeDeleteListBySource('dldb');
        (new \BO\Zmsdb\RequestRelation())->writeImportList(self::$repository->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            print("RequestRelation: Took $time seconds\n\n");
        }
    }

    protected function writeLastUpdate()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Config())->replaceProperty('sources_dldb_last', date('c'));
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            print("LastImportTimeToConfig: Took $time seconds\n\n");
        }
    }
}
