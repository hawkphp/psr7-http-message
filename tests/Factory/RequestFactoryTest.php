<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\RequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class RequestFactoryTest extends TestCase
{
    public function testRequestFactory()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('https://example.com', (string)$request->getUri());
    }
}
