<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream
 * @package Hawk\Psr7
 *
 * @link https://github.com/hawkphp/psr7-http-message GitHub main page
 *
 */
class Stream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $resource;

    /**
     * @var mixed
     */
    private $size;

    /**
     * @var array|null
     */
    private $meta;

    /**
     * @var bool
     */
    private $writable = false;

    /**
     * @var bool
     */
    private $readable = false;

    /**
     * @var bool
     */
    private $seekable = false;

    /**
     * @var array|mixed|null
     */
    private $uri;

    /**
     * Stream constructor
     * @param StreamInterface|null|resource $stream
     * @param array $metadata
     */
    public function __construct($stream, array $metadata = [])
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if (isset($metadata['size'])) {
            $this->size = $metadata['size'];
        }

        $this->meta = array_merge($metadata, stream_get_meta_data($stream));
        $this->resource = $stream;
        $mode = $this->meta['mode'];

        $this->writable = (bool)preg_match('/a|w|r\+|rb\+|rw|x|c/', $mode);
        $this->readable = (bool)preg_match('/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/', $mode);
        $this->seekable = fseek($this->resource, 0, SEEK_CUR) === 0 && $this->meta['seekable'];
        $this->uri = $this->getMetadata('uri');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (isset($this->resource)) {
            if (is_resource($this->resource)) {
                fclose($this->resource);
            }
            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = false;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
        $this->meta = null;
        $this->size = null;
        $this->uri = null;

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        if ($this->resource && !$this->size) {
            $stats = fstat($this->resource);
            $this->size = isset($stats['size']) ? $stats['size'] : null;
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        $result = ftell($this->resource);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->resource ? feof($this->resource) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable() || $this->resource && fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (!$this->isSeekable() || $this->resource && rewind($this->resource) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $written = false;

        if ($this->resource) {
            $written = fwrite($this->resource, $string);
        }

        if ($written === false) {
            throw new RuntimeException('Could not write to stream.');
        }

        $this->size = null;

        return $written;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $data = false;

        if ($this->resource) {
            $data = fread($this->resource, $length);
        }

        if (!is_string($data)) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->resource)) {
            return is_null($key) ? null : [];
        }

        $meta = stream_get_meta_data($this->resource);

        if (!$key) {
            return $meta;
        }

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}
