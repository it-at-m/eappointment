<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Useraccount;
use BO\Zmsdb\Role as RoleRepository;

/**
 * @SuppressWarnings(Coupling)
 * @return String
 */
class UseraccountUpdate extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request, 2))->checkRights('useraccount');

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();

        // Normalize roles from the incoming payload before schema validation.
        // Historically, roles were inferred from numeric "Berechtigung" levels.
        // In the new model, we may receive either role names (strings) or role ids (integers).
        if (isset($input['roles']) && is_array($input['roles'])) {
            $normalizedRoles = [];
            $numericRoleIds = [];

            foreach ($input['roles'] as $value) {
                if (is_string($value)) {
                    $value = trim($value);
                    if ($value !== '') {
                        $normalizedRoles[$value] = true;
                    }
                    continue;
                }

                if (is_int($value)) {
                    $numericRoleIds[] = $value;
                    continue;
                }

                if (is_string($value) && ctype_digit($value)) {
                    $numericRoleIds[] = (int) $value;
                }
            }

            if (!empty($numericRoleIds)) {
                $roleRepository = new RoleRepository();
                $namesById = $roleRepository->readRoleNamesByIds($numericRoleIds);
                foreach ($namesById as $name) {
                    $name = trim($name);
                    if ($name !== '') {
                        $normalizedRoles[$name] = true;
                    }
                }
            }

            $input['roles'] = array_keys($normalizedRoles);
        }

        $entity = new \BO\Zmsentities\Useraccount($input);
        if (! (new Useraccount())->readIsUserExisting($args['loginname'])) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        $this->testEntity($entity, $input, $args);

        if (isset($entity->changePassword)) {
            $entity->password = $entity->getHash(reset($entity->changePassword));
        }

        $message = Response\Message::create($request);
        $message->data = (new Useraccount())->writeUpdatedEntity($args['loginname'], $entity, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testEntity($entity, $input, $args)
    {
        if (0 == count($input)) {
            throw new Exception\Useraccount\UseraccountInvalidInput();
        }
        try {
            $entity->testValid('de_DE', 1);
        } catch (\Exception $exception) {
            $exception->data['input'] = $input;
            throw $exception;
        }

        if ($args['loginname'] != $entity->id && (new Useraccount())->readIsUserExisting($entity->id)) {
            throw new Exception\Useraccount\UseraccountAlreadyExists();
        }

        Helper\UserAuth::testUseraccountExists($args['loginname']);
        Helper\User::testWorkstationAccessRights($entity);
    }
}
