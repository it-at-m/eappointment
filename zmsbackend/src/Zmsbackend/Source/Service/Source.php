<?php

namespace BO\Zmsbackend\Source\Service;

use BO\Zmsbackend\Application as App;
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
class Source extends \BO\Zmsbackend\Base
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
            $query = new \BO\Zmsbackend\Source\Repository\Source(\BO\Zmsbackend\Query\Base::SELECT);
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
        $query = new \BO\Zmsbackend\Source\Repository\Source(\BO\Zmsbackend\Query\Base::SELECT);
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
    #[\Override]
    public function readResolvedReferences(
        \BO\Zmsentities\Schema\Entity $entity,
        $resolveReferences,
        $disableCache = false
    ) {
        if (0 < $resolveReferences) {
            $entity['providers'] = (new ProviderList())
                ->addList((new \BO\Zmsbackend\Provider\Service\Provider())->readListBySource($entity->source, $resolveReferences - 1));
            $entity['requests'] = (new RequestList())
                ->addList((new \BO\Zmsbackend\Request\Service\Request())->readListBySource(
                    $entity->source,
                    $resolveReferences - 1,
                    $disableCache
                ));
            $entity['requestrelation'] = (new RequestRelationList())
                ->addList((new \BO\Zmsbackend\RequestRelation\Service\RequestRelation())->readListBySource($entity->source));
            $entity['scopes'] = (new ScopeList())
                ->addList((new \BO\Zmsbackend\Scope\Service\Scope())->readList($disableCache));
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
            throw new \BO\Zmsbackend\Source\Exception\SourceInvalidInput();
        }
        $this->writeDeleteBySource($entity->getSource());
        $query = new \BO\Zmsbackend\Source\Repository\Source(\BO\Zmsbackend\Query\Base::INSERT);
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
        (new \BO\Zmsbackend\Provider\Service\Provider())->writeListBySource($entity);
        (new \BO\Zmsbackend\Request\Service\Request())->writeListBySource($entity);
        (new \BO\Zmsbackend\RequestRelation\Service\RequestRelation())->writeListBySource($entity);
    }

    /**
     * delete by sourcename
     *
     * @return \BO\Zmsentities\Source
     */
    public function writeDeleteBySource($sourceName)
    {
        $entity = $this->readEntity($sourceName);
        $query = new \BO\Zmsbackend\Source\Repository\Source(\BO\Zmsbackend\Query\Base::DELETE);
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
        (new \BO\Zmsbackend\Provider\Service\Provider())->writeDeleteListBySource($sourceName);
        (new \BO\Zmsbackend\Request\Service\Request())->writeDeleteListBySource($sourceName);
        (new \BO\Zmsbackend\RequestRelation\Service\RequestRelation())->writeDeleteListBySource($sourceName);
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
