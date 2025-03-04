<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use BO\Dldb\Exception;

/**
  * Common methods shared by access classes
  *
  */
abstract class Base
{
    protected $data = [];
    /**
     * lazy loaded item list, use getItemList() to access this
     *
     * @var \BO\Dldb\Collection\Base $itemList
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
     * @var \BO\Dldb\AbstractAccess $accessInstance
     */
    private $accessInstance = null;

    abstract protected function parseData($data);

    public function __construct($dataFile, $locale = "de")
    {
        $this->dataFile = $dataFile;
        $this->locale = $locale;
    }

    public function readDataFile()
    {
        if (empty($this->data)) {
            $jsonFile = $this->dataFile;
            if (!is_readable($jsonFile)) {
                throw new Exception("Cannot read $jsonFile");
            }
            $data = json_decode(file_get_contents($jsonFile), true);
            if (!$data) {
                throw new Exception("Could not decide $jsonFile");
            }
            $this->data = $data;
        }
        return $this->data;
    }

    public function getDataAsArray()
    {
        try {
            $data = $this->readDataFile();

            return $data['data'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getHash()
    {
        try {
            $data = $this->readDataFile();

            return $data['hash'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getData()
    {
        try {
            $data = $this->readDataFile();

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function loadData()
    {
        try {
            $data = $this->readDataFile();
            $this->itemList = $this->parseData($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getItemList()
    {
        if (null === $this->itemList) {
            $this->loadData();
        }
        return $this->itemList;
    }

    protected function setItemList($list)
    {
        $this->itemList = $list;
        return $this;
    }

    public function fetchId($itemId)
    {
        $itemList = $this->getItemList();

        if (! $itemId || !$itemList instanceof \BO\Dldb\Collection\Base || !$itemList->offsetExists($itemId)) {
            return false;
        }

        return $itemList[$itemId];
    }

    public function setAccessInstance(\BO\Dldb\AbstractAccess $accessInstance)
    {
        $this->accessInstance = $accessInstance;
    }

    public function access()
    {
        return $this->accessInstance;
    }
}
