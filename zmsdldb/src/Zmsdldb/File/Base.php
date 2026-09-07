<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Exception;

/**
  * Common methods shared by access classes
  *
  * @template TCollection of \BO\Zmsdldb\Collection\Base
  * @template TEntity of \BO\Zmsdldb\Entity\Base
  */
abstract class Base
{
    protected mixed $data = [];
    /**
     * lazy loaded item list, use getItemList() to access this
     *
     * @var TCollection|null $itemList
     */
    private $itemList = null;

    /**
     * @var String $dataFile
     */
    protected $dataFile = '';

    /**
     * @var String $locale Format like 'en'
     *
     */
    protected $locale = 'de';

    /**
     * @var \BO\Zmsdldb\AbstractAccess|null $accessInstance
     */
    private $accessInstance = null;

    /**
     * @return TCollection
     */
    abstract protected function parseData(mixed $data);

    public function __construct(mixed $dataFile, string $locale = "de")
    {
        $this->dataFile = $dataFile;
        $this->locale = $locale;
    }

    public function readDataFile(): mixed
    {
        if (empty($this->data)) {
            $jsonFile = $this->dataFile;
            if (!is_readable($jsonFile)) {
                throw new Exception("Cannot read $jsonFile");
            }
            $json = file_get_contents($jsonFile);
            if ($json === false) {
                throw new Exception("Cannot read $jsonFile");
            }
            $data = json_decode($json, true);
            if (!$data) {
                throw new Exception("Could not decide $jsonFile");
            }
            $this->data = $data;
        }
        return $this->data;
    }

    /** @psalm-api */
    public function getDataAsArray(): mixed
    {
        try {
            $data = $this->readDataFile();

            return $data['data'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** @psalm-api */
    public function getHash(): mixed
    {
        try {
            $data = $this->readDataFile();

            return $data['hash'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** @psalm-api */
    public function getData(): mixed
    {
        try {
            $data = $this->readDataFile();

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function loadData()
    {
        try {
            $data = $this->readDataFile();
            $this->itemList = $this->parseData($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return TCollection
     */
    public function getItemList()
    {
        if (null === $this->itemList) {
            $this->loadData();
        }
        return $this->itemList;
    }

    protected function setItemList(\BO\Zmsdldb\Collection\Base $list): static
    {
        /** @var TCollection $list */
        $this->itemList = $list;
        return $this;
    }

    /**
     * @return TEntity|false
     */
    public function fetchId(string $itemId)
    {
        $itemList = $this->getItemList();

        if (! $itemId || !$itemList->offsetExists($itemId)) {
            return false;
        }

        /** @var TEntity $item */
        $item = $itemList[$itemId];
        return $item;
    }

    public function setAccessInstance(\BO\Zmsdldb\AbstractAccess $accessInstance): void
    {
        $this->accessInstance = $accessInstance;
    }

    public function access(): \BO\Zmsdldb\AbstractAccess
    {
        if (!$this->accessInstance instanceof \BO\Zmsdldb\AbstractAccess) {
            throw new Exception('Access instance is not initialized');
        }
        return $this->accessInstance;
    }
}
