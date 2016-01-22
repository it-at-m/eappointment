<?php

namespace BO\Zmsdb\Query;

use \Solution10\SQL\Select;
use \Solution10\SQL\Insert;
use \Solution10\SQL\Update;
use \Solution10\SQL\Delete;
use \Solution10\SQL\Dialect\MySQL;

class Scope extends Base
{
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const REPLACE = 'REPLACE';
    const DELETE = 'DELETE';

    /**
     * @var \Solution10\SQL\Query $query
     */
    protected $query = null;

    public function __construct($queryType)
    {
        $dialect = new MySQL();
        if (self::SELECT === $queryType) {
            $this->query = new Select($dialect);
        }
    }

    public function getSql()
    {
        return (string)$this->query;
    }

    public function getParameters()
    {
        return $this->query->params();
    }
}
