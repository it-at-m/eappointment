<?php

namespace BO\Zmsdb\Source;

/**
 * @codeCoverageIgnore
 */
class Dldb extends \BO\Zmsdb\Base
{
    public static $importPath = '';
    public static $repository = null;

    public function startImport($verbose = true)
    {
        if (!static::$importPath) {
            throw new \Exception('No data path given');
        }
        if ($verbose) {
            echo "Use source-path for dldb: ". static::$importPath . "\n";
        }
        self::$repository = new \BO\Dldb\FileAccess();
        self::$repository->loadFromPath(static::$importPath);
        $repo = self::$repository;

        \BO\Zmsdb\Connection\Select::setTransaction();

        $startTime = microtime(true);
        $this->replaceRequests($repo->fromService()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if ($verbose) {
            echo "Requests: Took $time seconds\n";
        }

        $startTime = microtime(true);
        $this->replaceProvider($repo->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if ($verbose) {
            echo "Provider: Took $time seconds\n";
        }

        $startTime = microtime(true);
        $this->replaceRequestProvider($repo->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if ($verbose) {
            echo "RequestProvider: Took $time seconds\n";
        }

        \BO\Zmsdb\Connection\Select::writeCommit();
    }

    public function replaceRequestProvider($providerList)
    {
        $this->getWriter()->exec('DELETE FROM `request_provider`;');
        $sql = 'REPLACE INTO `request_provider` SET
            `source`=:source,
            `request__id`=:request__id,
            `provider__id`=:provider__id,
            `slots`=:slots;
        ';
        $query = $this->getWriter()->prepare($sql);
        foreach ($providerList as $provider) {
            // Do not import locations without address
            if ($provider['address']['postal_code']) {
                foreach ($provider['services'] as $reference) {
                    $query->bindValue('source', 'dldb');
                    $query->bindValue('provider__id', $provider['id']);
                    $query->bindValue('request__id', $reference['service']);
                    $query->bindValue('slots', $reference['appointment']['slots']);
                    $query->execute();
                }
            }
        }
    }

    public function replaceProvider($providerList)
    {
        $this->getWriter()->exec('DELETE FROM `provider`;');
        $sql = 'REPLACE INTO `provider` SET
            `source`=:source,
            `id`=:id,
            `name`=:name,
            `contact__city`=:contact__city,
            `contact__country`=:contact__country,
            `contact__lat`=:contact__lat,
            `contact__lon`=:contact__lon,
            `contact__postalCode`=:contact__postalCode,
            `contact__region`=:contact__region,
            `contact__street`=:contact__street,
            `contact__streetNumber`=:contact__streetNumber,
            `link`=:link,
            `data`=:data;
        ';
        $query = $this->getWriter()->prepare($sql);
        foreach ($providerList as $provider) {
            // Do not import locations without address
            if ($provider['address']['postal_code']) {
                $query->bindValue('source', 'dldb');
                $query->bindValue('id', $provider['id']);
                $query->bindValue('name', $provider['name']);
                $query->bindValue('contact__city', $provider['address']['city']);
                $query->bindValue('contact__country', $provider['address']['city']);
                $query->bindValue('contact__lat', $provider['geo']['lat']);
                $query->bindValue('contact__lon', $provider['geo']['lon']);
                $query->bindValue('contact__postalCode', intval($provider['address']['postal_code']));
                $query->bindValue('contact__region', $provider['address']['city']);
                $query->bindValue('contact__street', $provider['address']['street']);
                $query->bindValue('contact__streetNumber', $provider['address']['house_number']);
                //$query->bindValue('link', $provider['meta']['url']);
                $query->bindValue('link', "https://service.berlin.de/standort/${provider['id']}/");
                $query->bindValue('data', json_encode($provider));
                $query->execute();
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function replaceRequests($serviceList)
    {
        $this->getWriter()->exec('DELETE FROM `request`;');
        $sql = 'REPLACE INTO `request` SET
            `source`=:source,
            `id`=:id,
            `name`=:name,
            `link`=:link,
            `group`=:group,
            `data`=:data;
        ';
        $query = $this->getWriter()->prepare($sql);
        foreach ($serviceList as $service) {
            $topic = self::$repository->fromTopic()->fetchId($service['relation']['root_topic']);
            $query->bindValue('source', 'dldb');
            $query->bindValue('id', $service['id']);
            $query->bindValue('name', $service['name']);
            //$query->bindValue('link', $service['meta']['url']);
            $query->bindValue('link', "https://service.berlin.de/dienstleistung/${service['id']}/");
            $query->bindValue('group', $topic['name'] ? $topic['name'] : 'Sonstiges');
            $query->bindValue('data', json_encode($service));
            $query->execute();
        }
    }
}
