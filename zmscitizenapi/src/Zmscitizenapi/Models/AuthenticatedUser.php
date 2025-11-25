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

    private static function base64UrlDecode(string $data): string
    {
        $replaced = strtr($data, '-_', '+/');
        $pad = strlen($replaced) % 4;
        if ($pad) {
            $replaced .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($replaced) ?: '';
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
        $payload = json_decode(self::base64UrlDecode($tokenParts[1]), true);

        $instance = new self();
        if (empty($payload[\App::ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME])) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Property `' . \App::ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME . '` is missing from the JWT payload.');
        }
        $instance->setExternalUserId($payload[\App::ZMS_CITIZENLOGIN_EXTERNALUSERID_CLAIM_NAME]);
        if (!empty($payload['email'])) {
            $instance->setEmail($payload['email']);
        }
        if (!empty($payload['given_name'])) {
            $instance->setGivenName($payload['given_name']);
        }
        if (!empty($payload['family_name'])) {
            $instance->setGivenName($payload['family_name']);
        }
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
