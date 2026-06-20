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
    public ?int $processId;
    public ?string $timestamp;
    public ?string $authKey;
    public ?string $familyName;
    public ?string $customTextfield;
    public ?string $customTextfield2;
    public ?string $email;
    public ?string $telephone;
    public ?string $officeName;
    public ?int $officeId;
    public ?ThinnedScope $scope;
    public array $subRequestCounts;
    public ?int $serviceId;
    public ?string $serviceName;
    public int $serviceCount;
    public ?string $status;
    public ?string $captchaToken;
    public ?int $slotCount;
    public ?string $displayNumber;
    public ?string $icsContent;

    public function __construct(?int $processId = null, ?string $timestamp = null, ?string $authKey = null, ?string $familyName = null, ?string $customTextfield = null, ?string $customTextfield2 = null, ?string $email = null, ?string $telephone = null, ?string $officeName = null, ?int $officeId = null, ?ThinnedScope $scope = null, array $subRequestCounts = [], ?int $serviceId = null, ?string $serviceName = null, int $serviceCount = 0, ?string $status = null, ?string $captchaToken = null, ?int $slotCount = null, ?string $displayNumber = null, ?string $icsContent = null)
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
        $this->displayNumber = $displayNumber;
        $this->icsContent = $icsContent;
        $this->ensureValid();
    }

    public function toArray(): array
    {
        return [
            'processId' => $this->processId ?? null,
            'timestamp' => $this->timestamp ?? null,
            'authKey' => $this->authKey ?? null,
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
            'slotCount' => $this->slotCount ?? null,
            'displayNumber' => $this->displayNumber ?? null,
            'icsContent' => $this->icsContent ?? null
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

    public function setIcsContent(string $icsContent): void
    {
        $this->icsContent = $icsContent;
    }

    public function getIcsContent(): ?string
    {
        return $this->icsContent;
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
