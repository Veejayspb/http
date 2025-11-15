<?php

declare(strict_types=1);

namespace Veejay\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    /**
     * Errors list.
     * @link https://www.php.net/manual/en/features.file-upload.errors.php#115746
     */
    protected const ERRORS = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];

    /**
     * Path to file.
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * Stream object.
     * @var StreamInterface|null
     */
    protected ?StreamInterface $stream = null;

    /**
     * Error code.
     * @var int
     * @see ERRORS
     */
    protected int $error;

    /**
     * File size.
     * @var int|null
     */
    protected ?int $size;

    /**
     * Filename sent by the client.
     * @var string|null
     */
    protected ?string $clientFilename;

    /**
     * Media type sent by the client.
     * @var string|null
     */
    protected ?string $clientMediaType;

    /**
     * File already moved.
     * @var bool
     */
    protected bool $moved = false;

    /**
     * @param string|resource|StreamInterface $pathOrStream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @throw InvalidArgumentException
     */
    public function __construct(mixed $pathOrStream, ?int $size = null, int $error = UPLOAD_ERR_OK, ?string $clientFilename = null, ?string $clientMediaType = null)
    {
        if (!array_key_exists($error, self::ERRORS)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" - not valid error status. It must be one of "UPLOAD_ERR_*" constants: %s.',
                $error,
                implode('", "', array_keys(self::ERRORS))
            ));
        }

        if (is_string($pathOrStream) && (!is_file($pathOrStream) || !is_readable($pathOrStream))) {
            throw new InvalidArgumentException(sprintf(
                '"%s" - file not exists or is not readable.',
                $pathOrStream
            ));
        }

        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($error !== UPLOAD_ERR_OK) {
            return;
        }

        if (is_string($pathOrStream)) {
            $this->path = $pathOrStream;
        } elseif (is_resource($pathOrStream)) {
            $this->stream = new Stream($pathOrStream);
        } elseif ($pathOrStream instanceof StreamInterface) {
            $this->stream = $pathOrStream;
        } else {
            throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        $this->checkIsValid();

        if (!is_null($this->stream)) {
            return $this->stream;
        }

        $resource = fopen($this->path, 'r+');

        if ($resource === false) {
            throw new RuntimeException(sprintf(
                'The file "%s" cannot be opened: %s',
                $this->path,
                $this->getLastError()
            ));
        }

        return $this->stream = new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo(string $targetPath): void
    {
        $this->checkIsValid();
        $dir = dirname($targetPath);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException('Target directory is not writable');
        }

        if (is_null($this->path)) {
            $this->copyStream($targetPath);
        } else {
            $this->moveFile($targetPath);
        }

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
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
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * Check if is moved or upload status is not ok.
     * @return void
     * @throws RuntimeException
     */
    protected function checkIsValid(): void
    {
        if ($this->moved) {
            throw new RuntimeException('The file has already been moved.');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error occurred');
        }
    }

    /**
     * Move file to the specific target path.
     * @param string $targetPath
     * @return void
     * @throws RuntimeException
     */
    private function moveFile(string $targetPath): void
    {
        $isCliEnv = $this->isCliEnv();
        $result = $isCliEnv ? @rename($this->path, $targetPath) : @move_uploaded_file($this->path, $targetPath);

        if ($result !== false) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Uploaded file could not be moved to "%s": %s',
            $targetPath,
            $this->getLastError()
        ));
    }

    /**
     * Copy data from stream to destination path.
     * @param string $targetPath
     * @return void
     */
    private function copyStream(string $targetPath): void
    {
        $dest = fopen($targetPath, 'w');

        if ($dest === false) {
            throw new RuntimeException('Could not open target file for writing.');
        }

        $stream = $this->getStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $resource = $stream->detach();

        if (is_null($resource)) {
            throw new RuntimeException('Could not detach stream');
        }

        $result = stream_copy_to_stream($resource, $dest);

        if ($result === false) {
            throw new RuntimeException('Could not copy to stream.');
        }

        fclose($dest);
        fclose($resource);
    }

    /**
     * Check if is CLI environment.
     * @return bool
     */
    private function isCliEnv(): bool
    {
        if (!defined('PHP_SAPI')) {
            return true;
        }

        return str_starts_with(PHP_SAPI, 'cli');
    }

    /**
     * Get the last occurred error message.
     * @return string
     */
    private function getLastError(): string
    {
        $errorData = error_get_last();
        return $errorData['message'] ?? '';
    }
}
