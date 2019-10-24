<?php declare(strict_types=1);

namespace Hawk\Psr7;

use Hawk\Psr7\Factory\StreamFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UploadedFile
 *
 * @package Hawk\Psr7
 *
 * @link https://github.com/hawkphp/psr7-http-message GitHub main page
 *
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var bool
     */
    private $moved = false;

    /**
     * @var int
     */
    private $error = UPLOAD_ERR_OK;

    /**
     * @var bool
     */
    private $sapi = false;

    /**
     * UploadedFile constructor.
     * @param string $file
     * @param string|null $name
     * @param string|null $type
     * @param int|null $size
     * @param int $error
     * @param bool $sapi
     */
    public function __construct(
        string $file,
        ?string $name = null,
        ?string $type = null,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        bool $sapi = false
    ) {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    /**
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * @return bool
     */
    public function isUploaded(): bool
    {
        return UPLOAD_ERR_OK === $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isMoved()) {
            throw new \RuntimeException(
                sprintf('Unable to get stream, file %s has already been moved', $this->name)
            );
        }

        if (!$this->stream instanceof StreamInterface) {
            $this->stream = (new StreamFactory())->createStreamFromFile($this->file);
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath): UploadedFileInterface
    {
        if ($this->moved) {
            throw new \RuntimeException('The uploaded file has already been moved');
        }

        $this->moveActions($targetPath);

        $this->moved = true;

        return $this;
    }

    /**
     * @param string $targetPath
     */
    private function moveActions($targetPath): void
    {
        $isUrlStream = stripos($targetPath, '://');
        $isUrlStream = ($isUrlStream !== false && $isUrlStream > 0) ? true : false;

        if (!$isUrlStream && !is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException('Upload target path is not writable');
        }

        if ($isUrlStream) {
            $this->copyStream($targetPath);
        } elseif ($this->sapi !== false) {
            $this->moveFile($targetPath);
        } else {
            $this->renameFile($targetPath);
        }
    }

    /**
     * @param string $targetPath
     */
    private function renameFile($targetPath): void
    {
        if (!rename($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath)
            );
        }
    }

    /**
     * @param string $targetPath
     */
    private function copyStream($targetPath): void
    {
        if (!copy($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath)
            );
        }

        if (!unlink($this->file)) {
            throw new \RuntimeException(
                sprintf('Error removing uploaded file %s', $this->name)
            );
        }
    }

    /**
     * @param string $targetPath
     */
    private function moveFile($targetPath): void
    {
        if (!is_uploaded_file($this->file)) {
            throw new \RuntimeException(
                sprintf('%s is not a valid uploaded file', $this->file)
            );
        }

        if (!move_uploaded_file($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->name, $targetPath)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }
}
