<?php

namespace BO\Zmsdb\Tests\Helper;

/**
*
* @SuppressWarnings(Methods)
* @SuppressWarnings(Complexity)
 */
class ProcessQuery extends \BO\Zmsdb\Query\Process
{
    public function getLockProcessId()
    {
        return 'SELECT * FROM `' . self::getTablename() . '` A
          WHERE A.`BuergerID` = "UnitTest" FOR UPDATE';
    }
}
