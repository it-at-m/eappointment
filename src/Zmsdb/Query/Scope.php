<?php

namespace BO\Zmsdb\Query;

class Scope extends Base
{
    public function __construct($queryType)
    {
        parent::__construct($queryType);
        $this->query->from('standort', 'scope');
    }

    public function setEntityMapping()
    {
        $this->query->select('*');
    }


}
