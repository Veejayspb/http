<?php

declare(strict_types=1);

namespace Test\Unit;

use Psr\Http\Message\StreamInterface;
use Test\TestCase;
use Veejay\Http\Stream;
use Veejay\Http\UploadedFile;

final class UploadedFileTest extends TestCase
{
    protected const SIZE = 123;
    protected const CLIENT_FILE_NAME = 'name.txt';
    protected const CLIENT_MEDIA_TYPE = 'text/plain';

    protected const FILE_PATH = __DIR__ . '/../Temp/' . self::CLIENT_FILE_NAME;
    protected const MOVE_TO_PATH = __DIR__ . '/../Temp/moved.txt';
    protected const FILE_CONTENT = 'Some file content';

    public function testConstruct()
    {
        // Upload error
        $exception = $this->catchException(function () {
            new UploadedFile(self::FILE_PATH, 1, -1);
        });
        $this->assertNotNull($exception);

        // File not exists
        $exception = $this->catchException(function () {
            new UploadedFile('path/does/not/exists');
        });
        $this->assertNotNull($exception);

        // Path set
        $file = $this->createFileWithPath();
        $this->assertNull($file->stream);
        $this->assertSame(self::FILE_PATH, $file->path);

        // Resource set
        $resource = $this->getResource();
        $file = $this->getUploadedFile($resource);
        $this->assertNull($file->path);
        $this->assertNotNull($file->stream);

        // Stream set
        $stream = new Stream($resource);
        $file = $this->getUploadedFile($stream);
        $this->assertNull($file->path);
        $this->assertSame($stream, $file->stream);
    }

    public function testGetStream()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        // Upload error
        $file = $this->getUploadedFile(self::FILE_PATH, 1, UPLOAD_ERR_CANT_WRITE);
        $exception = $this->catchException(function () use ($file) {
            $file->getStream();
        });
        $this->assertNotNull($exception);

        // File moved
        $file = $this->createFileWithPath();
        $file->moved = true;
        $exception = $this->catchException(function () use ($file) {
            $file->getStream();
        });
        $this->assertNotNull($exception);

        // Use Stream to get Stream
        $file = $this->createFileWithStream($stream);
        $actual = $file->getStream();
        $this->assertSame($stream, $actual);

        // Use path to get Stream
        $file = $this->createFileWithPath();
        $actual = $file->getStream();
        $this->assertSame(file_get_contents(self::FILE_PATH), $actual->getContents());
        $this->assertSame($file->getStream(), $file->getStream()); // The same object every time
    }

    public function testMoveTo()
    {
        // Upload error
        $file = $this->getUploadedFile(self::FILE_PATH, 1, UPLOAD_ERR_CANT_WRITE);
        $exception = $this->catchException(function () use ($file) {
            $file->moveTo(self::MOVE_TO_PATH);
        });
        $this->assertNotNull($exception);

        // File moved
        $file = $this->getUploadedFile(self::FILE_PATH);
        $file->moved = true;
        $exception = $this->catchException(function () use ($file) {
            $file->moveTo(self::MOVE_TO_PATH);
        });
        $this->assertNotNull($exception);

        // Directory does not exists
        $file = $this->getUploadedFile(self::FILE_PATH);
        $exception = $this->catchException(function () use ($file) {
            $file->moveTo('path/does/not/exists');
        });
        $this->assertNotNull($exception);
    }

    public function testGetSize()
    {
        $file = $this->createFileWithPath();
        $this->assertSame(self::SIZE, $file->getSize());
    }

    public function testGetError()
    {
        $file = $this->createFileWithPath();
        $this->assertSame(UPLOAD_ERR_OK, $file->getError());
    }

    public function testGetClientFilename()
    {
        $file = $this->createFileWithPath();
        $this->assertSame(self::CLIENT_FILE_NAME, $file->getClientFilename());
    }

    public function testGetClientMediaType()
    {
        $file = $this->createFileWithPath();
        $this->assertSame(self::CLIENT_MEDIA_TYPE, $file->getClientMediaType());
    }

    public function testCopyStream()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);
        $file = $this->createFileWithStream($stream);
        $file->moveTo(self::MOVE_TO_PATH);

        $this->assertTrue($file->moved);
        $this->assertFileExists(self::FILE_PATH);
        $this->assertFileExists(self::MOVE_TO_PATH);
        $this->assertStringEqualsFile(self::MOVE_TO_PATH, self::FILE_CONTENT);
    }

    public function testMoveFile()
    {
        $file = $this->createFileWithPath();
        $file->moveTo(self::MOVE_TO_PATH);

        $this->assertTrue($file->moved);
        $this->assertFileDoesNotExist(self::FILE_PATH);
        $this->assertFileExists(self::MOVE_TO_PATH);
        $this->assertStringEqualsFile(self::MOVE_TO_PATH, self::FILE_CONTENT);
    }

    /**
     * @param StreamInterface $stream
     * @return UploadedFile
     */
    protected function createFileWithStream(StreamInterface $stream)
    {
        return $this->getUploadedFile(
            $stream,
            self::SIZE,
            UPLOAD_ERR_OK,
            self::CLIENT_FILE_NAME,
            self::CLIENT_MEDIA_TYPE,
        );
    }

    /**
     * @return UploadedFile
     */
    protected function createFileWithPath()
    {
        return $this->getUploadedFile(
            self::FILE_PATH,
            self::SIZE,
            UPLOAD_ERR_OK,
            self::CLIENT_FILE_NAME,
            self::CLIENT_MEDIA_TYPE,
        );
    }

    /**
     * @return resource
     */
    protected function getResource()
    {
        return fopen(self::FILE_PATH, 'r');
    }

    /**
     * @param mixed $pathOrStream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFile
     */
    protected function getUploadedFile(mixed $pathOrStream, ?int $size = null, int $error = UPLOAD_ERR_OK, ?string $clientFilename = null, ?string $clientMediaType = null)
    {
        return new class($pathOrStream, $size, $error, $clientFilename, $clientMediaType) extends UploadedFile
        {
            public ?string $path = null;
            public ?StreamInterface $stream = null;
            public int $error;
            public ?int $size;
            public ?string $clientFilename;
            public ?string $clientMediaType;
            public bool $moved = false;
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(self::FILE_PATH, self::FILE_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(self::FILE_PATH);
        @unlink(self::MOVE_TO_PATH);
    }
}
