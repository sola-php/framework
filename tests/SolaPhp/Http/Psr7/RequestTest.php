<?php

namespace Tests\SolaPhp\Http\Psr7;

use SolaPhp\Http\Psr7\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected Request $request;

    public function setUp(): void
    {
        $this->request = new Request('GET', '/');
    }

    public function testConstructor()
    {
        $this->assertEquals('/', $this->request->getUri()->getPath());
    }
}
