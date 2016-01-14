<?php

namespace BO\Zmsclient;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Schema\Factory;

/**
 * Handle default response
 */
class Result
{
    /**
     * @var \Psr\Http\Message\ResponseInterface $response
     */
    protected $response;

    /**
     * @var \BO\Zmsentities\Schema\entity $entity
     */
    protected $entity = null;

    public function __construct(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->setResponse($response);
    }

    /**
     * Parse response and the object values
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return self
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;
        $this->testMeta();
        $body = Validator::value((string)$response->getBody())->isJson();
        $result = $body->getValue();
        if (array_key_exists("data", $result)) {
            $this->setData($result['data']);
        }
        return $this;
    }

    /**
     * Test meta data on errors
     *
     * @throws Exception
     */
    protected function testMeta()
    {
        $response = $this->response;
        $body = Validator::value((string)$response->getBody())->isJson();
        if ($body->hasFailed()) {
            throw new Exception('API-Call failed, JSON parsing with error: ' . implode('; ', $body->getMessages()));
        }
        $result = $body->getValue();
        if (!array_key_exists("meta", $result)) {
            throw new Exception('Missing "meta" value on result, API-Call failed.');
        }
        $entity = Factory::create($result['meta'])->getEntity();
        if ($entity->error == true) {
            throw new Exception('API-Error: ' . $entity->message);
        }
    }

    /**
     * Get the origin response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Description
     *
     * @return \BO\Zmsentities\Schema\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity from response
     *
     * @param Array $data
     *
     * @return self
     */
    public function setData(Array $data)
    {
        $this->entity = Factory::create($data)->getEntity();
        return $this;
    }
}
