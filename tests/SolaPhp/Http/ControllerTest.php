<?php

namespace Tests\SolaPhp\Http;

use PHPUnit\Framework\TestCase;
use SolaPhp\Http\Controller;

class ControllerTest extends TestCase
{
    public function testInstantiate()
    {
        $c = new Controller();
        $this->assertInstanceOf(Controller::class, $c);
    }
}
