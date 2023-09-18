<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Calldisplay as Entity;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Calldisplay extends Base
{

    /**
     * read Ticketprinter by comma separated buttonlist
     *
     * @param
     * ticketprinter Entity
     * now DateTime
     *
     * @return Resource Entity
     */
    public function readResolvedEntity(Entity $calldisplay, \DateTimeImmutable $dateTime, $resolveReferences = 0)
    {
        if ($calldisplay->hasScopeList()) {
            $scopeList = new \BO\Zmsentities\Collection\ScopeList();
            foreach ($calldisplay->scopes as $key => $entity) {
                $query = new Scope();
                $scope = $query->readEntity($entity['id'], $resolveReferences - 1);
                /* test in zmsapi CalldisplayGet
                if (! $scope) {
                    throw new Exception\Calldisplay\ScopeNotFound();
                }
                */
                $scopeList->addEntity($scope);
            }
            $calldisplay->scopes = $scopeList;
        }
        if ($calldisplay->hasClusterList()) {
            $clusterList = new \BO\Zmsentities\Collection\ClusterList();
            foreach ($calldisplay->clusters as $key => $entity) {
                $query = new Cluster();
                $cluster = $query->readEntity($entity['id'], $resolveReferences);
                /* test in zmsapi CalldisplayGet
                if (! $cluster) {
                    throw new Exception\Calldisplay\ClusterNotFound();
                }
                */
                $clusterList->addEntity($cluster);
            }
            $calldisplay->clusters = $clusterList;
        }
        $calldisplay->setServerTime($dateTime->getTimestamp());
        $calldisplay->organisation = $this->readResolvedOrganisation($calldisplay);
        $calldisplay->image = $this->readImage($calldisplay);
        $calldisplay->contact = $this->readContactData($calldisplay);
        return $calldisplay->withOutClusterDuplicates();
    }

    public function readResolvedOrganisation(Entity $entity)
    {
        $organisation = null;
        $query = new Organisation();
        $scope = $entity->getScopeList()->getFirst();
        $cluster = $entity->getClusterList()->getFirst();
        if ($scope) {
            $organisation = $query->readByScopeId($scope->id);
        } elseif ($cluster) {
            $organisation = $query->readByClusterId($cluster->id);
        }
        return $organisation;
    }

    public function readImage(Entity $entity)
    {
        $name = $entity->getImageName();
        $image = null;
        if ($name) {
            $image = $this->getReader()
                ->fetchOne((new Query\Calldisplay(Query\Base::SELECT))
                ->getQueryImage(), ['name' => "$name%"]);
        }
        if (! $image) {
            $image = $this->getReader()
            ->fetchOne((new Query\Calldisplay(Query\Base::SELECT))
                ->getQueryImage(), ['name' => "logo.png"]);
        }
        $mime = pathinfo($image['name'], PATHINFO_EXTENSION);
        $image['mime'] = ($mime == 'jpg') ? 'jpeg' : $mime;
        return $image;
    }

    public function readContactData(Entity $entity)
    {
        $contact = new \BO\Zmsentities\Contact();
        if ($entity->hasClusterList() && 1 == $entity->getClusterList()->count()) {
            $contact->name = $entity->getClusterList()->getFirst()->name;
        } elseif ($entity->hasScopeList() && 1 == $entity->getScopeList()->count()) {
            $department = (new Department())->readByScopeId($entity->getScopeList()->getFirst()->id);
            $contact->name = $department->name;
        }
        return $contact;
    }
}
