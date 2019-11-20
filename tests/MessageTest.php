<?php

namespace Hawk\Tests\Psr7;

use Hawk\Tests\Psr7\Mocks\MessageMock;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class MessageTest
 * @package Hawk\Tests\Psr7
 */
class MessageTest extends TestCase implements MessageInterface
{
    public function getProtocolVersion()
    {
        $message = new MessageMock();

        $this->assertSame("1.0", $message->protocolVersion);

        $message->protocolVersion = "1.0";
        $this->assertSame("1.0", $message->protocolVersion);
    }

    public function withProtocolVersion($version)
    {
        // TODO: Implement withProtocolVersion() method.
    }

    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }


    public function hasHeader($name)
    {
        // TODO: Implement hasHeader() method.
    }


    public function getHeader($name)
    {
        // TODO: Implement getHeader() method.
    }


    public function getHeaderLine($name)
    {
        // TODO: Implement getHeaderLine() method.
    }


    public function withHeader($name, $value)
    {
        // TODO: Implement withHeader() method.
    }


    public function withAddedHeader($name, $value)
    {
        // TODO: Implement withAddedHeader() method.
    }

    public function withoutHeader($name)
    {
        // TODO: Implement withoutHeader() method.
    }


    public function getBody()
    {
        // TODO: Implement getBody() method.
    }


    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }
}
