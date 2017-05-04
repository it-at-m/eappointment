<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Archive as Entity;
use \BO\Zmsentities\Collection\ArchiveList as Collection;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Complexity)
 */
class Archive extends Base
{
    public function readEntity($archiveId = null, $resolveReferences = 0)
    {
        if (null === $archiveId) {
            return null;
        }
        $query = new Query\Archive(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionArchiveId($archiveId);
        $archive = $this->fetchOne($query, new Entity());
        $archive = $this->readResolvedReferences($archive, $resolveReferences);
        return $archive;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $archive, $resolveReferences)
    {
        if (1 <= $resolveReferences) {
            $archive['scope'] = (new Scope)->readEntity($archive->scope['id'], $resolveReferences - 1);
        }
        return $archive;
    }

    /**
     * write a new archived process to DB
     *
     */
    public function writeNewArchivedProcess(\BO\Zmsentities\Process $process, \DateTimeInterface $now)
    {
        $query = new Query\Archive(Query\Base::INSERT);
        $query->addValuesNewArchive($process, $now);
        $this->writeItem($query);
        $archiveId = $this->getWriter()->lastInsertId();
        Log::writeLogEntry("CREATE (Archive::writeNewArchivedArchive) $process ", $process->id);
        return $this->readEntity($archiveId);
    }

    /**
     * Read archiveList by scopeId and DateTime
     *
     * @param
     * scopeId
     * dateTime
     *
     * @return Collection archiveList
     */
    public function readListByScopeAndTime($scopeId, \DateTimeInterface $dateTime, $resolveReferences = 0)
    {
        $query = new Query\Archive(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionTime($dateTime);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement, $resolveReferences);
    }

    /**
     * Delete archive entry by archiveId
     *
     * @param
     *            archiveId
     *
     * @return Resource Status
     */
    public function deleteEntity($archiveId)
    {
        $query = Query\Archive(Query\Base::DELETE);
        $query->addConditionArchiveId($archiveId);
        $status = $this->deleteItem($query);
        Log::writeLogEntry("DELETE (Archive::deleteEntity) $archiveId ", $archiveId);
        return $status;
    }

    protected function readList($statement, $resolveReferences)
    {
        $query = new Query\Archive(Query\Base::SELECT);
        $archiveList = new Collection();
        while ($archiveData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postArchive($archiveData));
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
            $archiveList->addEntity($entity);
        }
        return $archiveList;
    }
}
