<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Source as Entity;
use BO\Zmsentities\Collection\SourceList as Collection;
use BO\Zmsentities\Collection\ProviderList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\RequestRelationList;

/**
 *
 * @SuppressWarnings(Coupling)
 *
 */
class Source extends Base
{
    /**
     * Fetch source from db
     *
     * @return \BO\Zmsentities\Source
     */
    public function readEntity($sourceName, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "source-$sourceName-$resolveReferences";

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            $entity = App::$cache->get($cacheKey);
        }

        if (empty($entity)) {
            $query = new Query\Source(Query\Base::SELECT);
            $query
                ->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionSource($sourceName);
            $entity = $this->fetchOne($query, new Entity());
            if (! $entity->hasId()) {
                return null;
            }

            if (App::$cache) {
                App::$cache->set($cacheKey, $entity);
            }
        }

        return $this->readResolvedReferences($entity, $resolveReferences, $disableCache);
    }

    /**
     * read a list of sources
     *
     * @return \BO\Zmsentities\Collection\SourceList
     */
    public function readList($resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\Source(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $entity = $this->readResolvedReferences($entity, $resolveReferences);
                    $collection->addEntity($entity);
                }
            }
        }
        return $collection;
    }

    /**
     * resolve entity references
     *
     * @return \BO\Zmsentities\Source
     */
    public function readResolvedReferences(
        \BO\Zmsentities\Schema\Entity $entity,
        $resolveReferences,
        $disableCache = false
    ) {
        if (0 < $resolveReferences) {
            $entity['providers'] = (new ProviderList())
                ->addList((new Provider())->readListBySource($entity->source, $resolveReferences - 1));
            $entity['requests'] = (new RequestList())
                ->addList((new Request())->readListBySource(
                    $entity->source,
                    $resolveReferences - 1,
                    $disableCache
                ));
            $entity['requestrelation'] = (new RequestRelationList())
                ->addList((new RequestRelation())->readListBySource($entity->source));
            $entity['scopes'] = (new ScopeList())
                ->addList((new Scope())->readList($disableCache));
        }

        return $entity;
    }

    /**
     * write or update a source
     *
     * @return \BO\Zmsentities\Source
     */
    public function writeEntity(Entity $entity, $resolveReferences = 0)
    {
        if (! $entity->isCompleteAndEditable()) {
            throw new Exception\Source\SourceInvalidInput();
        }
        $this->writeDeleteBySource($entity->getSource());
        $query = new Query\Source(Query\Base::INSERT);
        $query->addValues(
            array(
                'source' => $entity->getSource(),
                'label' => $entity->getLabel(),
                'editable' => ($entity->isEditable()) ? 1 : 0,
                'contact__name' => $entity->contact['name'],
                'contact__email' => $entity->contact['email']
            )
        );
        if ($this->writeItem($query)) {
            $this->writeInsertRelations($entity);
        }

        $this->removeCache($entity->getSource());

        return $this->readEntity($entity->getSource(), $resolveReferences, true);
    }

    /**
     * delete provider and request relations of source
     *
     */
    public function writeInsertRelations(\BO\Zmsentities\Source $entity)
    {
        (new Provider())->writeListBySource($entity);
        (new Request())->writeListBySource($entity);
        (new RequestRelation())->writeListBySource($entity);
    }

    /**
     * delete by sourcename
     *
     * @return \BO\Zmsentities\Source
     */
    public function writeDeleteBySource($sourceName)
    {
        $entity = $this->readEntity($sourceName);
        $query = new Query\Source(Query\Base::DELETE);
        $query->addConditionSource($sourceName);
        $this->writeDeleteRelations($sourceName);

        $this->removeCache($sourceName);

        return ($this->deleteItem($query)) ? $entity : null;
    }

    /**
     * delete provider and request relations of source
     *
     */
    public function writeDeleteRelations($sourceName)
    {
        (new Provider())->writeDeleteListBySource($sourceName);
        (new Request())->writeDeleteListBySource($sourceName);
        (new RequestRelation())->writeDeleteListBySource($sourceName);
    }

    public function removeCache($sourceName)
    {
        if (!App::$cache) {
            return;
        }

        if (App::$cache->has("source-$sourceName-0")) {
            App::$cache->delete("source-$sourceName-0");
        }

        if (App::$cache->has("source-$sourceName-1")) {
            App::$cache->delete("source-$sourceName-1");
        }

        if (App::$cache->has("source-$sourceName-2")) {
            App::$cache->delete("source-$sourceName-2");
        }
    }
}
