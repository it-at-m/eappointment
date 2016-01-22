<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;

class Request extends Base
{
    public function readEntity($source, $requestId)
    {
        $entity = null;
        if ('dldb' !== $source) {
            return null;
        }
        $data = $this->getReader()->fetchOne(
            'SELECT
                d.id AS id,
                CONCAT("https://service.berlin.de/dienstleistung/", d.id, "/") AS link,
                d.name AS name,
                "dldb" AS source
            FROM startinfo.dienstleistungen AS d
            WHERE
                id = ?
            ',
            [$requestId]
        );
        if ($data) {
            $entity = new Entity($data);
        }
        return $entity;
    }
}
