<?php

namespace BO\Zmsadmin\Helper;

use BO\Zmsentities\Exception\SchemaValidation;
use BO\Zmsentities\Role as RoleEntity;
use Psr\Http\Message\RequestInterface;

class RoleInputHelper
{
    /**
     * Parsed role form fields from POST body (name, description, permissions).
     */
    public static function readFormInput(RequestInterface $request): array
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }
        $name = isset($body['name']) ? trim((string) $body['name']) : '';
        $description = isset($body['description']) ? trim((string) $body['description']) : '';
        $perms = $body['permissions'] ?? [];
        if (!is_array($perms)) {
            $perms = $perms !== null && $perms !== '' ? [$perms] : [];
        }
        $perms = array_values(array_filter(array_map('strval', $perms)));

        return [
            'name' => $name,
            'description' => $description === '' ? null : $description,
            'permissions' => $perms,
        ];
    }

    /**
     * Build a Role entity from form-derived $input and run schema validation.
     */
    public static function validateAndCreateEntity(array $input, callable $transformValidationErrors): RoleEntity|array
    {
        $entity = new RoleEntity($input);
        try {
            $entity->testValid();
        } catch (SchemaValidation $e) {
            return [
                'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig',
                'include' => true,
                'data' => $transformValidationErrors($e->data),
            ];
        }

        return $entity;
    }
}
