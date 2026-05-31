<?php

/**
 * @package ClientDLDB
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Importer;

use BO\Zmsdldb\SQLiteAccess;
use BO\Zmsdldb\FileAccess
;

class SQLite extends Base
{
    public function __construct(SQLiteAccess $sqLiteAccess, FileAccess $fileAccess, int $options = 0)
    {
        try {
            parent::__construct($sqLiteAccess, $fileAccess, $options);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function runImport()
    {
        try {
            parent::runImport();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
    /**
     * @return void
     */
    public function preImport()
    {
        $this->beginTransaction();
    }

    /**
     * @return void
     */
    public function postImport()
    {
        $this->commit();
    }
}
