<?php

namespace BO\Zmsclient\Tests;

use BO\Zmsclient\Http;
use BO\Zmsclient\Psr7\Client;
use BO\Zmsclient\Psr7\Request;
use BO\Zmsclient\Psr7\Uri;
use BO\Slim\Middleware\Validator;
use \BO\Zmsclient\GraphQL\GraphQLInterpreter;
use Slim\Psr7\Factory\StreamFactory;

class GraphQLTest extends Base
{

    
    /*public function testBasic()
    {
        $uri = new Uri(self::$http_baseurl . '/process/82252/12a2/?gql={id,amendment}');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);

        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));

        $body = (new StreamFactory())->createStream();
        $body->write(json_encode($responseData));
        $response = new \BO\Slim\Response(200, null, $body);

        $this->assertStringContainsString('"id":"82252","amendment":""', (string)$response->getBody());
        $this->assertStringNotContainsString('scope', (string)$response->getBody());
        $this->assertStringNotContainsString('status', (string)$response->getBody());
    }

    public function testCollection()
    {
        $uri = new Uri(self::$http_baseurl . '/scope/?gql={id}');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);

        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));

        $body = (new StreamFactory())->createStream();
        $body->write(json_encode($responseData));
        $response = new \BO\Slim\Response(200, null, $body);

        $this->assertStringContainsString('"id":"123"', (string)$response->getBody());
        $this->assertStringNotContainsString('contact', (string)$response->getBody());
        $this->assertEquals(
            '[{"id":"123","$schema":"https:\/\/schema.berlin.de\/queuemanagement\/scope.json"}]',
            (string)$graphqlInterpreter
        );
    }

    public function testSubNode()
    {
        $uri = new Uri(self::$http_baseurl . '/process/82252/12a2/?gql={scope{id}}');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);

        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));

        $body = (new StreamFactory())->createStream();
        $body->write(json_encode($responseData));
        $response = new \BO\Slim\Response(200, null, $body);

        $this->assertStringNotContainsString('amendment', (string)$response->getBody());
        $this->assertStringContainsString('"scope":{"id":"141"}', (string)$response->getBody());
    }

    public function testUnvalidGraphQL()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('No valid graphql');
        $uri = new Uri(self::$http_baseurl . '/process/82252/12a2/?gql={');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);
        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));
    }

    public function testUnvalidContent()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('No content for graph');
        $uri = new Uri(self::$http_baseurl . '/process/82252/12a2/?gql=');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);
        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));
    }

    public function testNoParent()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('Curly bracket match problem, too many closing brackets');
        $uri = new Uri(self::$http_baseurl . '/process/82252/12a2/?gql={scope{id}}}');
        $request = Http::createRequest('GET', $uri);
        $request = Validator::withValidator($request);
       
        $validator = $request->getAttribute('validator');
        $gqlString = $validator->getParameter('gql')->isString()->getValue();
        $graphqlInterpreter = new GraphQLInterpreter($gqlString);

        $response = Client::readResponse($request);

        $responseData = json_decode((string)$response->getBody(), true);
        $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));
    }*/
}
