<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

/**
  * Common methods shared by access classes
  *
  */
abstract class Base
{
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

    abstract protected function parseData($data);

    public function __construct($dataFile)
    {
        $this->dataFile = $dataFile;
    }

    public function loadData()
    {
        $jsonFile = $this->dataFile;
        if (!is_readable($jsonFile)) {
            throw new Exception("Cannot read $jsonFile");
        }
        $data = json_decode(file_get_contents($jsonFile), true);
        if (!$data) {
            throw new Exception("Could not decide $jsonFile");
        }
        $this->itemList = $this->parseData($data);
    }

    public function getItemList()
    {
        if (null === $this->itemList) {
            $this->loadData();
        }
        return $this->itemList;
    }
}
