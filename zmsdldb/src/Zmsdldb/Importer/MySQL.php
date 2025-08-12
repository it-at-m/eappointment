<?php

/**
 * @package ClientDLDB
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Importer;

use BO\Zmsdldb\MySQLAccess;
use BO\Zmsdldb\FileAccess
;

class MySQL extends Base
{
    public function __construct(MySQLAccess $mysqlAccess, FileAccess $fileAccess, int $options = 0)
    {
        try {
            parent::__construct($mysqlAccess, $fileAccess, $options);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function runImport()
    {
        try {
            parent::runImport();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
    public function preImport()
    {
        $this->beginTransaction();
    }

    public function postImport()
    {
        $this->commit();
    }
}
