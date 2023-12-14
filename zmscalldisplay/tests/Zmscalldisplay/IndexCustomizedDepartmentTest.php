<?php

namespace BO\Zmscalldisplay\Tests;

class IndexCustomizedDepartmentTest extends Base
{
    protected $classname = "Index";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readPostResult',
                'url' => '/calldisplay/',
                'response' => $this->readFixture("GET_calldisplay_department_76.json")
            ]
        ];
    }

    /*public function testRendering()
    {
        $response = $this->render([ ], [
            'collections' => [
                'scopelist' => '146'
            ]
        ], [ ]);
        $this->assertStringContainsString('Tempelhof-Schöneberg', (string) $response->getBody());
        $this->assertStringContainsString('tableLayout.multiColumns="2"', (string) $response->getBody());
        $this->assertStringContainsString('tableLayout.maxResults=10', (string) $response->getBody());
    }*/

    /*public function testTemplateNotFound()
    {
        $this->expectException('\BO\Zmscalldisplay\Exception\TemplateNotFound');
        $this->expectExceptionCode(404);
        $this->render([], [
            'collections' => [
                'scopelist' => '146'
            ],
            'template' => 'notfound'
        ], [ ]);
    }*/
}
