<?php

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Request;
use Hawk\Psr7\Uri;
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
        $request = new Request('/', 'GET', [], 'test');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('test', (string)$request->getBody());
    }

    public function testNullBody()
    {
        $request = new Request('/', 'GET', [], null);
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('', (string)$request->getBody());
    }

    public function testGetBody()
    {
        $request = new Request('/', 'GET', [], 'test');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('test', (string)$request->getBody());
    }

    public function testWithUri()
    {
        $request = new Request('/');
        $uri = $request->getUri();
        $this->assertSame($uri, $request->getUri());

        $uri2 = new Uri('http://example.com');
        $request2 = $request->withUri($uri2);
        $this->assertNotSame($request, $request2);
        $this->assertSame($uri2, $request2->getUri());
        $this->assertSame($uri, $request->getUri());

        $request1 = new Request('http://example.com', 'GET');
        $request2 = $request1->withUri($request1->getUri());
        $this->assertSame($request1, $request2);
    }
}
