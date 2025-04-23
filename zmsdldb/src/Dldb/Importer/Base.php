<?php

/**
 * @package ClientDLDB
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Importer;

use BO\Dldb\PDOAccess;
use BO\Dldb\FileAccess
;

abstract class Base implements Options
{
    use PDOTrait;
    use OptionsTrait;

    protected $fileAccess;

    protected $localeList = [
        'de',
        'en'
    ];

    protected $importTypes = [
        'Services' => 'Service',
        'Locations' => 'Location',
        'Authorities' => 'Authority',
        'Topics' => 'Topic',
        'Settings' => 'Setting'
    ];

    public function __construct(PDOAccess $pdoAccess, FileAccess $fileAccess, int $options = 0)
    {
        $this->setPDOAccess($pdoAccess);
        $this->fileAccess = $fileAccess;
        $this->options = $options;
    }

    public function setLocaleList(array $localeList = ['de', 'en'])
    {
        $this->localeList = $localeList;
    }

    public function __call($method, $args = [])
    {
        try {
            if (preg_match('/get(?P<importer>[A-Za-z]+)Importer/', $method, $matches)) {
                $ImporterClass = static::class . '\\' . $matches['importer'];
                array_unshift($args, $this->getPDOAccess());
                $instance = new $ImporterClass(...$args);
                return $instance;
            }
            throw new \BadMethodCallException('Method ' . $method . ' not found!');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return self
     */
    public function runImport()
    {
        try {
            $this->preImport();

            foreach ($this->importTypes as $type => $accessor) {
                if (method_exists($this, 'import' . $type)) {
                    call_user_func([$this, 'import' . $type]);
                    continue;
                }
                foreach ($this->localeList as $locale) {
                    $entitys = call_user_func_array([$this->fileAccess, 'from' . $accessor], [$locale])->getData();
                    $importer = $this->__call(
                        'get' . $type . 'Importer',
                        [$entitys, $locale, $this->getOptions()]
                    );
                    $importer->preImport();
                    $importer->runImport();
                    $importer->postImport();
                    #$importer->clearEntity();
                }
            }

            $this->postImport();
            return $this;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearDatabase()
    {
        try {
            if (
                $this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES) ||
                $this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_TABLE)
            ) {
                /*
                $types = [
                    'Services',
                    'Locations',
                    'Authorities',
                    'Topics',
                    'Settings'
                ];
                */
                $tablesToClear = [];

                foreach (array_keys($this->importTypes) as $type) {
                    $importer = $this->__call(
                        'get' . $type . 'Importer',
                        [
                            ['data' => [], 'hash' => ''],
                            'de',
                            $this->getOptions()
                        ]
                    );
                    $entity = $importer->createEntity(['meta' => ['locale' => 'de']], false);
                    $tablesToClear[$entity::getTableName()] = 1;

                    foreach (array_values($entity->getReferenceMapping(true)) as $data) {
                        $tablesToClear[call_user_func($data['class'] . '::getTableName')] = 1;
                    }
                }

                foreach (array_flip($tablesToClear) as $table) {
                    $this->getPDOAccess()->exec("DELETE FROM " . $table . " WHERE 1=1");
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return self
     */
    protected function importSettings()
    {
        try {
            $importer = $this->getSettingsImporter(
                $this->fileAccess->fromSetting('de')->getData(),
                'de',
                $this->getOptions()
            );
            $importer->preImport();
            $importer->runImport();
            $importer->postImport();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return self
     */
    protected function importTopics()
    {
        try {
            $importer = $this->getTopicsImporter(
                $this->fileAccess->fromTopic('de')->getData(),
                'de',
                $this->getOptions()
            );
            $importer->preImport();
            $importer->runImport();
            $importer->postImport();
            #$importer->clearEntity();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return self
     */
    protected function importAuthorities()
    {
        try {
            $importer = $this->getAuthoritiesImporter(
                $this->fileAccess->fromAuthority('de')->getData(),
                'de',
                $this->getOptions()
            );
            $importer->preImport();
            $importer->runImport();
            $importer->postImport();
            #$importer->clearEntity();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function preImport()
    {
    }

    public function postImport()
    {
    }
}
