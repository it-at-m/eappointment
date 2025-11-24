<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }

    public function getSchemaConstraintList($schema)
    {
        $list = [];
        $locale = \App::$language->getLocale();
        foreach ($schema->properties as $key => $property) {
            if (isset($property['x-locale'])) {
                $constraints = $property['x-locale'][$locale];
                if ($constraints) {
                    $list[$key]['description'] = $constraints['messages'];
                }
            }
        }
        return $list;
    }

    /**
     * Transform validation error data from JSON pointer format to field names
     * Maps pointers like "/id" to "id", "/changePassword" to "changePassword", etc.
     * Handles nested paths like "/contact/email" by flattening them.
     * Also handles data that's already in field name format (for test compatibility).
     *
     * @param array|null $errorData The exception data with JSON pointers as keys, or field names
     * @return array Transformed data with field names as keys
     */
    protected function transformValidationErrors($errorData)
    {
        if (!is_array($errorData) && !($errorData instanceof \Traversable)) {
            return [];
        }
        $transformed = [];
        foreach ($errorData as $pointer => $item) {
            // Extract field name from JSON pointer (e.g., "/id" -> "id", "/contact/email" -> "contact/email")
            // If the key doesn't start with "/", it's already a field name, so use it as-is
            $fieldName = (strpos($pointer, '/') === 0) ? ltrim($pointer, '/') : $pointer;
            // Handle root level errors
            if ($fieldName === '' || $fieldName === null) {
                $fieldName = '_root';
            }
            // Ensure the item structure is correct (has 'messages' array)
            if (is_array($item) && isset($item['messages'])) {
                $transformed[$fieldName] = $item;
            } elseif (is_array($item)) {
                // If item is an array but doesn't have 'messages', wrap it
                $transformed[$fieldName] = $item;
            } else {
                $transformed[$fieldName] = $item;
            }
        }
        return $transformed;
    }
}
