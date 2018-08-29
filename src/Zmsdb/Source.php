<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Source as Entity;

use \BO\Zmsentities\Collection\ProviderList;

use \BO\Zmsentities\Collection\RequestList;

class Source extends Base
{
    /**
     * Fetch source from db
     *
     * @return \BO\Zmsentities\Source
     */
    public function readEntity($sourceName, $resolveReferences = 0)
    {
        $query = new Query\Source(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionSource($sourceName);
        $entity = $this->fetchOne($query, new Entity());
        $entity = $this->readResolvedReferences($entity, $resolveReferences);
        return $entity;
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
        $resolveReferences
    ) {
        if (0 < $resolveReferences) {
            $entity['providers'] = (new ProviderList())->readBySource($entity->source, $resolveReferences);
            $entity['requests'] = (new RequestList())->readBySource($entity->source, $resolveReferences);
        }
        return $entity;
    }


    /**
     * write a source
     *
     * @return \BO\Zmsentities\Source
     */
    public function writeEntity(Entity $entity)
    {
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
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * update a source
     *
     *
     * @return \BO\Zmsentities\Source
     */
    public function updateEntity(Entity $entity, $resolveReferences = 0)
    {
        $query = new Query\Source(Query\Base::UPDATE);
        $query->addConditionSource($entity->getSource());
        $query->addValues(
            array(
                'label' => $entity->getLabel(),
                'editable' => ($entity->isEditable()) ? 1 : 0,
                'contact__name' => $entity->contact['name'],
                'contact__email' => $entity->contact['email']
            )
        );
        $this->writeItem($query);
        return $this->readEntity($entity->getSource(), $resolveReferences);
    }
}
