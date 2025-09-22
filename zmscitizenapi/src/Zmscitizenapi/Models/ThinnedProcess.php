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
    public ?string $customTextfield2;
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
/** @var string|null */
    public ?string $serviceName;
/** @var int */
    public int $serviceCount;
/** @var string|null */
    public ?string $status;
/** @var string|null */
    public ?string $captchaToken;
/** @var int|null */
    public ?int $slotCount;

    public function __construct(?int $processId = null, ?string $timestamp = null, ?string $authKey = null, ?string $familyName = null, ?string $customTextfield = null, ?string $customTextfield2 = null, ?string $email = null, ?string $telephone = null, ?string $officeName = null, ?int $officeId = null, ?ThinnedScope $scope = null, array $subRequestCounts = [], ?int $serviceId = null, ?string $serviceName = null, int $serviceCount = 0, ?string $status = null, ?string $captchaToken = null, ?int $slotCount = null)
    {
        $this->processId = $processId;
        $this->timestamp = $timestamp;
        $this->authKey = $authKey;
        $this->familyName = $familyName;
        $this->customTextfield = $customTextfield;
        $this->customTextfield2 = $customTextfield2;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->officeName = $officeName;
        $this->officeId = $officeId;
        $this->scope = $scope;
        $this->subRequestCounts = $subRequestCounts;
        $this->serviceId = $serviceId;
        $this->serviceName = $serviceName;
        $this->serviceCount = $serviceCount;
        $this->status = $status;
        $this->captchaToken = $captchaToken;
        $this->slotCount = $slotCount;
        $this->ensureValid();
    }

    /**
     * Convert the ThinnedProcess object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $encrypted = self::encryptAuthToken($this->authKey);

        return [
            'processId' => $this->processId ?? null,
            'timestamp' => $this->timestamp ?? null,
            'authKey' => $this->authKey ?? null,
            'authKeyGenerated' => $encrypted ?? null,
            'authKeyGeneratedReverse' => self::decryptAuthToken($encrypted) ?? null,
            'familyName' => $this->familyName ?? null,
            'customTextfield' => $this->customTextfield ?? null,
            'customTextfield2' => $this->customTextfield2 ?? null,
            'email' => $this->email ?? null,
            'telephone' => $this->telephone ?? null,
            'officeName' => $this->officeName ?? null,
            'officeId' => $this->officeId ?? null,
            'scope' => $this->scope ?? null,
            'subRequestCounts' => $this->subRequestCounts,
            'serviceId' => $this->serviceId ?? null,
            'serviceName' => $this->serviceName ?? null,
            'serviceCount' => $this->serviceCount,
            'status' => $this->status ?? null,
            'captchaToken' => $this->captchaToken ?? null,
            'slotCount' => $this->slotCount ?? null
        ];
    }

    public function setCaptchaToken(string $token): void
    {
        $this->captchaToken = $token;
    }

    public function getCaptchaToken(): ?string
    {
        return $this->captchaToken;
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
