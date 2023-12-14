<?php

namespace BO\Zmscalldisplay\Tests;

class OldRedirectTest extends Base
{

    protected $classname = "RedirectOld";

    protected $arguments = [ ];

    protected $parameters = [ ];

    /*public function testRendering()
    {
        $response = $this->render([ ], [
            'auswahlstandortid' => array(141,142),
            'auswahlclusterid' => array(109),
            'OID' => 71
        ], [ ]);
        $this->assertRedirect($response, '/?collections%5Bscopelist%5D=141%2C142&collections%5Bclusterlist%5D=109');
    }*/
}
