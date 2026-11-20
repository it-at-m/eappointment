<?php

namespace BO\Zmsentities\Schema;

use BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(NumberOfChildren)
 */
class Factory
{
    /**
     * @var mixed $data unserialized entity
     */
    protected $data = null;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public static function create(mixed $data): self
    {
        return new self($data);
    }

    /**
     * Detect type of entity and return initialized object
     *
     * @return Entity
     */
    public function getEntity()
    {
        $class = self::entityClass($this->getEntityName());
        /** @var Entity $entity */
        $entity = new $class(new UnflattedArray($this->data));
        return $entity;
    }

    /**
     * Schema names are the lowercased class, with the first letter restored.
     * A few older names only capitalize that first letter.
     *
     * @return class-string<Entity>
     */
    private static function entityClass(string $entityName): string
    {
        $classes = [
            'Apiclient' => \BO\Zmsentities\Apiclient::class,
            'Apikey' => \BO\Zmsentities\Apikey::class,
            'Appointment' => \BO\Zmsentities\Appointment::class,
            'Availability' => \BO\Zmsentities\Availability::class,
            'AvailabilityHistory' => \BO\Zmsentities\AvailabilityHistory::class,
            'Calendar' => \BO\Zmsentities\Calendar::class,
            'Calldisplay' => \BO\Zmsentities\Calldisplay::class,
            'Client' => \BO\Zmsentities\Client::class,
            'Closure' => \BO\Zmsentities\Closure::class,
            'Cluster' => \BO\Zmsentities\Cluster::class,
            'Config' => \BO\Zmsentities\Config::class,
            'Contact' => \BO\Zmsentities\Contact::class,
            'Day' => \BO\Zmsentities\Day::class,
            'Dayoff' => \BO\Zmsentities\Dayoff::class,
            'Department' => \BO\Zmsentities\Department::class,
            'Eventlog' => \BO\Zmsentities\EventLog::class,
            'Exchange' => \BO\Zmsentities\Exchange::class,
            'Ics' => \BO\Zmsentities\Ics::class,
            'Link' => \BO\Zmsentities\Link::class,
            'Log' => \BO\Zmsentities\Log::class,
            'Mail' => \BO\Zmsentities\Mail::class,
            'Mailtemplate' => \BO\Zmsentities\Mailtemplate::class,
            'Metaresult' => \BO\Zmsentities\Metaresult::class,
            'Mimepart' => \BO\Zmsentities\Mimepart::class,
            'Month' => \BO\Zmsentities\Month::class,
            'Organisation' => \BO\Zmsentities\Organisation::class,
            'Owner' => \BO\Zmsentities\Owner::class,
            'Permission' => \BO\Zmsentities\Permission::class,
            'Process' => \BO\Zmsentities\Process::class,
            'Processarchived' => \BO\Zmsentities\Processarchived::class,
            'Provider' => \BO\Zmsentities\Provider::class,
            'Queue' => \BO\Zmsentities\Queue::class,
            'Request' => \BO\Zmsentities\Request::class,
            'Requestrelation' => \BO\Zmsentities\RequestRelation::class,
            'Requeststatistic' => \BO\Zmsentities\Requeststatistic::class,
            'Role' => \BO\Zmsentities\Role::class,
            'Scope' => \BO\Zmsentities\Scope::class,
            'Session' => \BO\Zmsentities\Session::class,
            'Slot' => \BO\Zmsentities\Slot::class,
            'Source' => \BO\Zmsentities\Source::class,
            'Status' => \BO\Zmsentities\Status::class,
            'Ticketprinter' => \BO\Zmsentities\Ticketprinter::class,
            'Useraccount' => \BO\Zmsentities\Useraccount::class,
            'Useraccountprofile' => \BO\Zmsentities\Useraccountprofile::class,
            'Workstation' => \BO\Zmsentities\Workstation::class,
        ];

        if (!isset($classes[$entityName])) {
            throw new \InvalidArgumentException('Unknown schema entity');
        }

        return $classes[$entityName];
    }

    /**
     * Parse schema and return Entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        if (!Property::__keyExists('$schema', $this->data)) {
            throw new \BO\Zmsentities\Exception\SchemaMissingKey('Missing $schema-key on given data.');
        }
        $schema = $this->data['$schema'];
        $entityName = is_string($schema)
            ? (preg_replace('#^.*/([^/]+)\.json#', '$1', $schema) ?? '')
            : '';
        $entityName = ucfirst($entityName);

        // jsonSerialize() lowercases the class short name (AvailabilityHistory →
        // availabilityhistory.json). ucfirst then yields Availabilityhistory, which
        // PSR-4 cannot autoload on Linux.
        $classAliases = [
            'Availabilityhistory' => 'AvailabilityHistory',
        ];

        return $classAliases[$entityName] ?? $entityName;
    }
}
