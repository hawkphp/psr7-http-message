<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class StreamFactory
 * @package Hawk\Psr7\Factory
 *
 * @link https://github.com/hawkphp/psr7-http-message GitHub main page
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rw+');

        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);

        if (!is_resource($resource)) {
            throw new RuntimeException(sprintf("Could not create resource from file %s", $filename));
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(
                'Parameter 1 of StreamFactory::createStreamFromResource() must be a resource.'
            );
        }

        return new Stream($resource);
    }
}
