<?php

declare(strict_types=1);

namespace Veejay\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    protected const MODE_READ = 0b01;
    protected const MODE_WRITE = 0b10;
    protected const MODE_READ_WRITE = self::MODE_READ | self::MODE_WRITE;

    protected const MODES = [
        'r' => self::MODE_READ,
        'w' => self::MODE_WRITE,
        'a' => self::MODE_WRITE,
        'x' => self::MODE_WRITE,
        'c' => self::MODE_WRITE,

        'r+' => self::MODE_READ_WRITE,
        'w+' => self::MODE_READ_WRITE,
        'a+' => self::MODE_READ_WRITE,
        'x+' => self::MODE_READ_WRITE,
        'c+' => self::MODE_READ_WRITE,

        'rt' => self::MODE_READ,
        'wt' => self::MODE_WRITE,
        'at' => self::MODE_WRITE,
        'xt' => self::MODE_WRITE,
        'ct' => self::MODE_WRITE,

        'rb' => self::MODE_READ,
        'wb' => self::MODE_WRITE,
        'ab' => self::MODE_WRITE,
        'xb' => self::MODE_WRITE,
        'cb' => self::MODE_WRITE,

        'r+t' => self::MODE_READ_WRITE,
        'w+t' => self::MODE_READ_WRITE,
        'a+t' => self::MODE_READ_WRITE,
        'x+t' => self::MODE_READ_WRITE,
        'c+t' => self::MODE_READ_WRITE,

        'r+b' => self::MODE_READ_WRITE,
        'w+b' => self::MODE_READ_WRITE,
        'a+b' => self::MODE_READ_WRITE,
        'x+b' => self::MODE_READ_WRITE,
        'c+b' => self::MODE_READ_WRITE,
    ];

    /**
     * @var resource
     */
    protected mixed $resource;

    /**
     * Stream metadata associative array.
     * @var array
     */
    protected array $meta = [];

    /**
     * @param resource $resource
     * @throws InvalidArgumentException
     */
    public function __construct(mixed $resource)
    {
        if (!is_resource($resource) || get_resource_type($resource) != 'stream') {
            throw new InvalidArgumentException('Resource must be a valid stream');
        }

        $this->resource = $resource;
        $this->meta = stream_get_meta_data($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (!is_null($this->resource)) {
            fclose($this->resource);
            $this->clearObject();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->clearObject();

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (is_null($this->resource)) {
            throw new RuntimeException('Stream is detached');
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
        if (is_null($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable') ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');

        if (!is_string($mode)) {
            return false;
        }

        $bitmask = self::MODES[$mode] ?? 0;

        return ($bitmask & self::MODE_WRITE) === self::MODE_WRITE;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Cannot write to non-writable stream');
        }

        if (is_null($this->resource)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');

        if (!is_string($mode)) {
            return false;
        }

        $bitmask = self::MODES[$mode] ?? 0;

        return ($bitmask & self::MODE_READ) === self::MODE_READ;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if (is_null($this->resource)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if (is_null($this->resource)) {
            throw new RuntimeException('Stream is detached');
        }

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(?string $key = null)
    {
        $isNullKey = is_null($key);

        if (is_null($this->resource)) {
            return $isNullKey ? [] : null;
        }

        if ($isNullKey) {
            return $this->meta;
        }

        return $this->meta[$key] ?? null;
    }

    /**
     * Clear properties of current object.
     * @return void
     */
    protected function clearObject(): void
    {
        $this->resource = null;
        $this->meta = [];
    }
}
