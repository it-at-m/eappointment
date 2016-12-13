<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Calldisplay as Entity;

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
    public function readResolvedEntity(Entity $calldisplay, \DateTimeImmutable $dateTime)
    {
        if ($calldisplay->hasScopeList()) {
            $scopeList = new \BO\Zmsentities\Collection\ScopeList();
            foreach ($calldisplay->scopes as $key => $entity) {
                $query = new Scope();
                $scope = $query->readEntity($entity['id']);
                if (! $scope) {
                    throw new Exception\Calldisplay\ScopeNotFound();
                }
                $scopeList->addEntity($scope);
            }
            $calldisplay->scopes = $scopeList;
        }
        if ($calldisplay->hasClusterList()) {
            $clusterList = new \BO\Zmsentities\Collection\ClusterList();
            foreach ($calldisplay->clusters as $key => $entity) {
                $query = new Cluster();
                $cluster = $query->readEntity($entity['id'], 1);
                if (! $cluster) {
                    throw new Exception\Calldisplay\ClusterNotFound();
                }
                $clusterList->addEntity($cluster);
            }
            $calldisplay->clusters = $clusterList;
        }
        $calldisplay->setServerTime($dateTime->getTimestamp());
        $calldisplay->organisation = $this->readResolvedOrganisation($calldisplay);
        $calldisplay->image = $this->readImage($calldisplay);
        return $calldisplay;
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
        $image = $this->getReader()
            ->fetchOne((new Query\Calldisplay(Query\Base::SELECT))
            ->getQueryImage(), ['name' => $name]);
        if (! $image) {
            $image = $this->getReader()
            ->fetchOne((new Query\Calldisplay(Query\Base::SELECT))
                ->getQueryImage(), ['name' => "baer.png"]);
        }
        $mime = pathinfo($image['name'], PATHINFO_EXTENSION);
        $image['mime'] = ($mime == 'jpg') ? 'jpeg' : $mime;
        return $image;
    }
}
