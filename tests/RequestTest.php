<?php

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class RequestTest
 * @package Hawk\Tests\Psr7
 */
class RequestTest extends TestCase
{
    public function testConstructUriAndMethod()
    {
        $request = new Request();
        $this->assertSame('', $request->getUri()->getPath());
        $this->assertEquals('GET', (string)$request->getMethod());
    }

    public function testConstructHeaders()
    {
        $request = new Request('/', 'POST', ['Accept' => 'Age']);
        $this->assertEquals(['Age'], $request->getHeader('Accept'));
    }

    public function testConstructWithBody()
    {
        $r = new Request('/', 'GET', [], 'test');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertEquals('test', (string)$r->getBody());
    }

    public function testNullBody()
    {
        $r = new Request('/', 'GET', [], null);
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('', (string)$r->getBody());
    }

    public function testFalseyBody()
    {
        $r = new Request('GET', '/', [], '0');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('0', (string)$r->getBody());
    }

    public function testConstructorDoesNotReadStreamBody()
    {
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();
        $body->expects($this->never())
            ->method('__toString');
        $r = new Request('GET', '/', [], $body);
        $this->assertSame($body, $r->getBody());
    }
}
