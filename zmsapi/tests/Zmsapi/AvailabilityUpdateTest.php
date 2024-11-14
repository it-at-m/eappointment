<?php

namespace BO\Zmsapi\Tests;

use \BO\Zmsentities\Availability as Entity;

use \BO\Zmsdb\Availability as Query;

class AvailabilityUpdateTest extends Base
{
    protected $classname = "AvailabilityUpdate";

    public function testRendering()
    {
        $input = (new Entity)->createExample();
    
        // Dynamically adjust the endDate to be in the future
        $currentTimestamp = time();
        $input['startDate'] = $currentTimestamp + (2 * 24 * 60 * 60); // Set startDate to 2 days in the future
        $input['endDate'] = $currentTimestamp + (5 * 24 * 60 * 60);   // Set endDate to 5 days in the future
    
        
    
        // Write the entity using the modified input
        $entity = (new Query())->writeEntity($input);
        error_log(json_encode($entity)); // Log for debugging
        $this->setWorkstation();
    
        // Prepare the response and test rendering
        $response = $this->render([
            "id" => $entity->getId()
        ], [
            '__body' => json_encode([
                "id" => $entity->getId(),
                "description" => "",
                "scope" => ["id" => 312]
            ])
        ], []);
        $this->assertStringContainsString('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
    

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Availability\AvailabilityNotFound');
        $this->expectExceptionCode(404);
        $this->render(["id"=> 1], [
            '__body' => '{
                  "id": 1,
                  "description": "Test Öffnungszeit not found",
                  "scope": {
                      "id": 312
                  }
              }'
        ], []);
    }
}
