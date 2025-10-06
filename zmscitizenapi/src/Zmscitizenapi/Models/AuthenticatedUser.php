<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmscitizenapi\Exceptions\InvalidAuthTokenException;
use JsonSerializable;

class AuthenticatedUser implements JsonSerializable
{
    private ?string $externalUserId;
    private ?string $givenName;
    private ?string $familyName;
    private ?string $email;

    public function __construct(
        ?string $externalUserId = null,
        ?string $email = null,
        ?string $givenName = null,
        ?string $familyName = null,
    ) {
        $this->externalUserId = $externalUserId;
        $this->email = $email;
        $this->givenName = $givenName;
        $this->familyName = $familyName;
    }

    public static function fromJwtPayload(?string $token): ?self
    {
        // Token is validated in API gateway
        if (is_null($token)) {
            return null;
        }
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Invalid JWT payload.');
        }
        $payload = json_decode(base64_decode($tokenParts[1]), true);

        $instance = new self();
        if (empty($payload['lhmExtID'])) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Property `lhmExtID` is missing from the JWT payload.');
        }
        $instance->setExternalUserId($payload['lhmExtID']);
        if (empty($payload['email'])) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Property `email` is missing from the JWT payload.');
        }
        $instance->setEmail($payload['email']);
        if (empty($payload['given_name'])) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Property `given_name` is missing from the JWT payload.');
        }
        $instance->setGivenName($payload['given_name']);
        if (empty($payload['family_name'])) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Property `family_name` is missing from the JWT payload.');
        }
        $instance->setFamilyName($payload['family_name']);
        return $instance;
    }

    public function getExternalUserId(): ?string
    {
        return $this->externalUserId;
    }

    public function setExternalUserId(?string $externalUserId): self
    {
        $this->externalUserId = $externalUserId;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;
        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'external_user_id' => $this->externalUserId,
            'email' => $this->email,
            'given_name' => $this->givenName,
            'family_name' => $this->familyName,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
