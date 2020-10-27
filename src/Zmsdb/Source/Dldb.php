<?php

namespace BO\Zmsdb\Source;

/**
 * @codeCoverageIgnore
 */
class Dldb extends \BO\Zmsdb\Base
{
    public static $importPath = '';
    public static $repository = null;
    public static $verbose = false;

    public function startImport($verbose = true)
    {
        if (!static::$importPath) {
            throw new \Exception('No data path given');
        }
        if ($verbose) {
            self::$verbose = $verbose;
            echo "Use source-path for dldb: ". static::$importPath . "\n";
        }
        self::$repository = new \BO\Dldb\FileAccess();
        self::$repository->loadFromPath(static::$importPath);

        \BO\Zmsdb\Connection\Select::setTransaction();

        $this->writeRequestList();
        $this->writeProviderList();
        $this->writeRequestRelationList();
        $this->writeLastUpdate($verbose);

        \BO\Zmsdb\Connection\Select::writeCommit();
    }

    protected function writeRequestList()
    {
        $startTime = microtime(true);
        $requestQuery = (new \BO\Zmsdb\Request());
        $requestQuery->writeDeleteListBySource('dldb');
        foreach (self::$repository->fromService()->fetchList() as $request) {
            $topic = self::$repository->fromTopic()->fetchId($request['relation']['root_topic']);
            $request['group'] = $topic['name'];
            $requestQuery->writeImportEntity($request);
        }
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "Requests: Took $time seconds\n";
        }
    }

    protected function writeProviderList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Provider())->writeDeleteListBySource('dldb');
        (new \BO\Zmsdb\Provider())->writeImportList(self::$repository->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "Provider: Took $time seconds\n";
        }
    }

    protected function writeRequestRelationList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\RequestRelation())->writeDeleteListBySource('dldb');
        (new \BO\Zmsdb\RequestRelation())->writeImportList(self::$repository->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "RequestRelation: Took $time seconds\n";
        }
    }

    protected function writeLastUpdate()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Config())->replaceProperty('sources_dldb_last', date('c'));
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "LastImportTimeToConfig: Took $time seconds\n";
        }
    }
}
