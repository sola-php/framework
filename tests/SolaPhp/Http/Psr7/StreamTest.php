<?php

namespace Tests\SolaPhp\Http\Psr7;

use SolaPhp\Http\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    protected string $testFilePath;

    public function setUp(): void
    {
        $this->testFilePath = dirname(__FILE__) . '/../../../fixtures/test.txt';
    }

    public function tearDown(): void
    {
        $file = fopen($this->testFilePath, 'w');
        fseek($file, 0);
        fwrite($file, 'hello sola');
        fclose($file);
    }

    public function testStreamReadable()
    {
        $file = fopen($this->testFilePath, 'r');
        $stream = new Stream($file);

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('hello sola', $stream->getContents());
        $this->assertEquals(10, $stream->getSize());

        $stream->close();
    }

    public function testStreamWritable()
    {
        $file = fopen($this->testFilePath, 'a+');
        $stream = new Stream($file);

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isWritable());
        $this->assertEquals('hello sola', $stream->getContents());
        $this->assertEquals(10, $stream->getSize());
        $stream->write(' world');
        $stream->close();

        $file = fopen($this->testFilePath, 'r');
        $stream = new Stream($file);
        $this->assertEquals('hello sola world', $stream->getContents());
        $stream->close();
    }
}
