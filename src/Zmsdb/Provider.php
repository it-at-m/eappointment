<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;

class Provider extends Base
{
    public function readEntity($source, $providerId)
    {
        $entity = null;
        if ('dldb' !== $source) {
            return null;
        }
        $data = $this->getReader()->fetchOne(
            'SELECT
                d.adr_ort AS contact__city,
                "Germany" AS contact__country,
                d.name AS contact__name,
                d.adr_plz AS contact__postalCode,
                d.adr_ort AS contact__region,
                d.adr_strasse AS contact__street,
                d.adr_hnr AS contact__streetNumber,
                d.id AS id,
                d.name AS name,
                "dldb" AS source,
                CONCAT("https://service.berlin.de/standort/", d.id, "/") AS link
            FROM startinfo.dienstleister AS d
            WHERE
                id = ?
            ',
            [$providerId]
        );
        if ($data) {
            $entity = new Entity($data);
        }
        return $entity;
    }
}
