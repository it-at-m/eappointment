<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmscitizenapi\Models\ThinnedProvider;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ThinnedScope extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/thinnedScope.json';
    /** @var int */
    public int $id;
    /** @var ThinnedProvider|null */
    public ?ThinnedProvider $provider;
    /** @var string|null */
    public ?string $shortName;
    /** @var string|null */
    public ?string $emailFrom;
    /** @var bool|null */
    public ?bool $emailRequired;
    /** @var bool|null */
    public ?bool $telephoneActivated;
    /** @var bool|null */
    public ?bool $telephoneRequired;
    /** @var bool|null */
    public ?bool $customTextfieldActivated;
    /** @var bool|null */
    public ?bool $customTextfieldRequired;
    /** @var string|null */
    public ?string $customTextfieldLabel;
    /** @var bool|null */
    public ?bool $captchaActivatedRequired;
    /** @var string|null */
    public ?string $displayInfo;
    public function __construct(int $id = 0, ?ThinnedProvider $provider = null, ?string $shortName = null, ?string $emailFrom = null, ?bool $emailRequired = null, ?bool $telephoneActivated = null, ?bool $telephoneRequired = null, ?bool $customTextfieldActivated = null, ?bool $customTextfieldRequired = null, ?string $customTextfieldLabel = null, ?bool $captchaActivatedRequired = null, ?string $displayInfo = null)
    {
        $this->id = $id;
        $this->provider = $provider;
        $this->shortName = $shortName;
        $this->emailFrom = $emailFrom;
        $this->emailRequired = $emailRequired;
        $this->telephoneActivated = $telephoneActivated;
        $this->telephoneRequired = $telephoneRequired;
        $this->customTextfieldActivated = $customTextfieldActivated;
        $this->customTextfieldRequired = $customTextfieldRequired;
        $this->customTextfieldLabel = $customTextfieldLabel;
        $this->captchaActivatedRequired = $captchaActivatedRequired;
        $this->displayInfo = $displayInfo;
        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    public function getProvider(): ?ThinnedProvider
    {
        return $this->provider;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function getEmailFrom(): ?string
    {
        return $this->emailFrom;
    }

    public function getEmailRequired(): ?bool
    {
        return $this->emailRequired;
    }

    public function getTelephoneActivated(): ?bool
    {
        return $this->telephoneActivated;
    }

    public function getTelephoneRequired(): ?bool
    {
        return $this->telephoneRequired;
    }

    public function getCustomTextfieldActivated(): ?bool
    {
        return $this->customTextfieldActivated;
    }

    public function getCustomTextfieldRequired(): ?bool
    {
        return $this->customTextfieldRequired;
    }

    public function getCustomTextfieldLabel(): ?string
    {
        return $this->customTextfieldLabel;
    }

    public function getCaptchaActivatedRequired(): ?bool
    {
        return $this->captchaActivatedRequired;
    }

    public function getDisplayInfo(): ?string
    {
        return $this->displayInfo;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'shortName' => $this->shortName,
            'emailFrom' => $this->emailFrom,
            'emailRequired' => $this->emailRequired,
            'telephoneActivated' => $this->telephoneActivated,
            'telephoneRequired' => $this->telephoneRequired,
            'customTextfieldActivated' => $this->customTextfieldActivated,
            'customTextfieldRequired' => $this->customTextfieldRequired,
            'customTextfieldLabel' => $this->customTextfieldLabel,
            'captchaActivatedRequired' => $this->captchaActivatedRequired,
            'displayInfo' => $this->displayInfo,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
