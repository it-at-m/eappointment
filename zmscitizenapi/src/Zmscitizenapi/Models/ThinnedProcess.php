<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ThinnedProcess extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/thinnedProcess.json";

    /** @var int|null */
    public ?int $processId;

    /** @var string|null */
    public ?string $timestamp;

    /** @var string|null */
    public ?string $authKey;

    /** @var string|null */
    public ?string $familyName;

    /** @var string|null */
    public ?string $customTextfield;

    /** @var string|null */
    public ?string $email;

    /** @var string|null */
    public ?string $telephone;

    /** @var string|null */
    public ?string $officeName;

    /** @var int|null */
    public ?int $officeId;

    /** @var ThinnedScope|null */
    public ?ThinnedScope $scope;

    /** @var array */
    public array $subRequestCounts;

    /** @var int|null */
    public ?int $serviceId;

    /** @var int */
    public int $serviceCount;

    /** @var string|null */
    public ?string $status;

    public function __construct(
        ?int $processId = null,
        ?string $timestamp = null,
        ?string $authKey = null,
        ?string $familyName = null,
        ?string $customTextfield = null,
        ?string $email = null,
        ?string $telephone = null,
        ?string $officeName = null,
        ?int $officeId = null,
        ?ThinnedScope $scope = null,
        array $subRequestCounts = [],
        ?int $serviceId = null,
        int $serviceCount = 0,
        ?string $status = null
    ) {
        $this->processId = $processId;
        $this->timestamp = $timestamp;
        $this->authKey = $authKey;
        $this->familyName = $familyName;
        $this->customTextfield = $customTextfield;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->officeName = $officeName;
        $this->officeId = $officeId;
        $this->scope = $scope;
        $this->subRequestCounts = $subRequestCounts;
        $this->serviceId = $serviceId;
        $this->serviceCount = $serviceCount;
        $this->status = $status;

        $this->ensureValid();
    }

    /**
     * Convert the ThinnedProcess object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'processId' => $this->processId ?? null,
            'timestamp' => $this->timestamp ?? null,
            'authKey' => $this->authKey ?? null,
            'familyName' => $this->familyName ?? null,
            'customTextfield' => $this->customTextfield ?? null,
            'email' => $this->email ?? null,
            'telephone' => $this->telephone ?? null,
            'officeName' => $this->officeName ?? null,
            'officeId' => $this->officeId ?? null,
            'scope' => $this->scope ?? null,
            'subRequestCounts' => $this->subRequestCounts,
            'serviceId' => $this->serviceId ?? null,
            'serviceCount' => $this->serviceCount,
            'status' => $this->status ?? null,
        ];
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}