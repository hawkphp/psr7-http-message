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
     * The mode parameter specifies the type of access you require to the stream.
     *
     * @see https://www.php.net/manual/en/function.fopen.php
     */
    private const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';

    /**
     *
     */
    private const WRITABLE_MODES = '/a|w|r\+|rb\+|rw|x|c/';

    /**
     * @var StreamInterface
     */
    private $stream;

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
     * @param array $options
     */
    public function __construct($stream, array $options = [])
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        if (isset($options['size'])) {
            $this->size = $options['size'];
        }

        $this->meta = array_merge($options['metadata'], stream_get_meta_data($this->stream));
        $this->stream = $stream;
        $this->seekable = $this->meta['seekable'];
        $this->readable = (bool)preg_match(self::READABLE_MODES, $this->meta['mode']);
        $this->writable = (bool)preg_match(self::WRITABLE_MODES, $this->meta['mode']);
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
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->stream;
        $this->stream = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
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
        if ($this->stream && !$this->size) {
            $stats = fstat($this->stream);
            $this->size = isset($stats['size']) ? $stats['size'] : null;
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        $result = ftell($this->stream);

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
        return $this->stream ? feof($this->stream) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable() || $this->stream && fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (!$this->isSeekable() || $this->stream && rewind($this->stream) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $written = false;

        if ($this->stream) {
            $written = fwrite($this->stream, $string);
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
    public function isReadable()
    {
        return $this->readable;
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
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $data = false;

        if ($this->stream) {
            $data = fread($this->stream, $length);
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
        $contents = false;

        if ($this->stream) {
            $contents = stream_get_contents($this->stream);
        }

        if (!is_string($contents)) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (!$this->stream) {
            return null;
        }

        if (!$key) {
            return $this->meta;
        }

        return array_key_exists($key, $this->meta) ? $this->meta[$key] : null;
    }
}
