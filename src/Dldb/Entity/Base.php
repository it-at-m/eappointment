<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

class Base
{
    /**
      * date for entity
      *
      * @var Array $data
      */
    protected $data;

    /**
     * @return self
     */
    public function __construct($data)
    {
        $this->setData($data);
    }

    /**
     * @return self
     */
    protected function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return Array
     */
    protected function getData()
    {
        return $this->data;
    }
}
