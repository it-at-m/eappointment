<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Apikey as Entity;

class Apikey extends Base
{
    public static $cache = [];

    public function readEntity($apiKey)
    {
        $query = new Query\Apikey(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences(0)
            ->addConditionApikey($apiKey);
        $entity = $this->fetchOne($query, new Entity());
        if ($entity->hasId()) {
            $entity->quota = $this->readQuotaList($apiKey);
        }
        return ($entity) ? $entity : null;
    }

    /**
     * write a new apikey
     *
     * @param
     *      entity
     *
     * @return Entity
     */
    public function writeEntity(Entity $entity)
    {
        $query = new Query\Apikey(Query\Base::INSERT);
        $query->addValues([
            'key' => $entity->key,
            'ts' => \App::$now->getTimestamp()
        ]);
        if ($this->writeItem($query)) {
            foreach ($entity->quota as $quota) {
                $this->writeQuota($entity->key, $quota['route'], $quota['period'], $quota['requests']);
            }
        }
        return $this->readEntity($entity->key);
    }

    /**
     * update an existing active apikey
     *
     * @param
     *      entity
     *
     * @return Entity
     */
    public function updateEntity($apiKey, Entity $entity)
    {
        $query = new Query\Apikey(Query\Base::UPDATE);
        $query->addConditionApikey($apiKey);
        $query->addValues([
            'ts' => \App::$now->getTimestamp()
        ]);
        $this->writeItem($query);
        $this->updateQuota($apiKey, $entity);
        return $this->readEntity($apiKey, 1);
    }

    /**
     * read specified api quota
     *
     * @param
     *      apiKey
     *      entity
     *
     * @return Entity
     */
    public function readQuota($apiKey, $route)
    {
        $data = $this->fetchRow(
            Query\Apiquota::getQueryReadApiQuota(),
            [
                'key' => $apiKey,
                'route' => $route
            ]
        );
        return $data;
    }

    /**
     * read api quotas by apikey
     *
     * @param
     *      apiKey
     *      entity
     *
     * @return Entity
     */
    public function readQuotaList($apiKey)
    {
        $data = $this
        ->getReader()
        ->fetchAll(
            Query\Apiquota::getQueryReadApiQuotaList(),
            [
                'key' => $apiKey
            ]
        );
        return ($data) ? $data : null;
    }

    /**
     * write initial api quotas
     *
     * @param
     *      entity
     *
     * @return Entity
     */
    public function writeQuota($apiKey, $route, $period, $requests)
    {
        $query = new Query\Apiquota(Query\Base::INSERT);
        $query->addValues([
            'key' => $apiKey,
            'route' => $route,
            'period' => $period,
            'requests' => $requests,
            'ts' => \App::$now->getTimestamp()
        ]);
        $this->writeItem($query);
    }

    /**
     * update api quotas
     *
     * @param
     *      apiKey
     *      entity
     *
     * @return Entity
     */
    public function updateQuota($apiKey, Entity $entity)
    {
        if (isset($entity->quota)) {
            foreach ($entity->quota as $quota) {
                if ($this->readQuota($apiKey, $quota['route'])) {
                    $query = new Query\Apiquota(Query\Base::UPDATE);
                    $query->addConditionApikey($apiKey);
                    $query->addConditionRoute($quota['route']);
                    $query->addValues([
                        'key' => $apiKey,
                        'route' => $quota['route'],
                        'period' => $quota['period'],
                        'requests' => $quota['requests']
                    ]);
                    $this->writeItem($query);
                } else {
                    $this->writeQuota($apiKey, $quota['route'], $quota['period'], $quota['requests']);
                }
            }
        }
    }

    /**
     * delete an existing outdated apikey
     *
     * @param
     *      apikey
     *
     * @return Entity
     */
    public function deleteEntity($apiKey)
    {
        $entity = $this->readEntity($apiKey);
        if ($entity) {
            $query = new Query\Apikey(Query\Base::DELETE);
            $query->addConditionApikey($apiKey);
            if ($this->deleteItem($query)) {
                $queryQuota = new Query\Apiquota(Query\Base::DELETE);
                $queryQuota->addConditionApikey($apiKey);
                $this->deleteItem($queryQuota);
            };
        }

        return ($entity) ? $entity : null;
    }
}
