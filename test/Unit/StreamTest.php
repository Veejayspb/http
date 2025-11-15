<?php

declare(strict_types=1);

namespace Test\Unit;

use ReflectionClass;
use stdClass;
use Test\TestCase;
use Veejay\Http\Stream;

final class StreamTest extends TestCase
{
    protected const FILE_PATH = __DIR__ . '/../Temp/temp.txt';
    protected const CONTENT = 'Some file content';
    protected const MODE = 'r+';

    public function testConstruct()
    {
        $resource = $this->getResource();
        $stream = new class($resource) extends Stream {
            public mixed $resource;
            public array $meta = [];
        };

        $this->assertSame($resource, $stream->resource);
        $this->assertNotEmpty($stream->meta);

        // Wrong values instead of resource
        $wrongValues = [
            1,
            1.2,
            'str',
            ['array'],
            new stdClass,
            null,
            true,
        ];

        foreach ($wrongValues as $value) {
            $exception = $this->catchException(function () use ($value) {
                new Stream($value);
            });
            $this->assertNotNull($exception);
        }
    }

    public function testToString()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $expected = $stream->getContents();
        $actual = (string)$stream;
        $this->assertSame($expected, $actual);
    }

    public function testClose()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);
        $stream->close();

        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getSize());
    }

    public function testDetach()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $this->assertSame($resource, $stream->detach());
        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getSize());
        $this->assertNull($stream->detach());
    }

    public function testGetSize()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $expected = filesize(self::FILE_PATH);
        $actual = $stream->getSize();
        $this->assertSame($expected, $actual);
    }

    public function testTell()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);
        $length = strlen(self::CONTENT);

        for ($i = 0; $i < $length + 10; $i++) {
            $stream->seek($i);
            $actual = $stream->tell();
            $this->assertSame($i, $actual);
        }
    }

    public function testEof()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $actual = $stream->eof();
        $this->assertFalse($actual);

        $stream->getContents();
        $actual = $stream->eof();
        $this->assertTrue($actual);
    }

    public function testIsSeekable()
    {
        $resource = $this->getResource();
        $stream = new class($resource) extends Stream {
            public array $meta = [];
        };

        $stream->meta = ['seekable' => true];
        $actual = $stream->isSeekable();
        $this->assertTrue($actual);

        $stream->meta = ['seekable' => false];
        $actual = $stream->isSeekable();
        $this->assertFalse($actual);
    }

    public function testSeek()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $stream->seek(1, SEEK_CUR);
        $expected = substr(self::CONTENT, 1);
        $actual = $stream->getContents();
        $this->assertSame($expected, $actual);

        $stream->seek(2, SEEK_SET);
        $expected = substr(self::CONTENT, 2);
        $actual = $stream->getContents();
        $this->assertSame($expected, $actual);

        $substr = 'new';
        $stream->seek(1, SEEK_END);
        $stream->write($substr);
        $expected = self::CONTENT . chr(0) . $substr;
        $actual = file_get_contents(self::FILE_PATH);
        $this->assertSame($expected, $actual);

        // Is not seekable
        $resource = $this->getResource();
        $stream = new class($resource) extends Stream {
            public array $meta = [];
        };

        $stream->meta = ['seekable' => false];
        $exception = $this->catchException(function () use ($stream) {
            $stream->seek(0);
        });
        $this->assertNotNull($exception);
    }

    public function testRewind()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $stream->seek(1);
        $stream->rewind();
        $actual = $stream->getContents();
        $this->assertSame(self::CONTENT, $actual);
    }

    public function testIsWritable()
    {
        $reflection = new ReflectionClass(Stream::class);
        $modes = $reflection->getConstant('MODES');

        foreach ($modes as $mode => $bitMask) {
            $resource = $this->getResource($mode);
            $stream = new Stream($resource);

            $expected = $bitMask === $reflection->getConstant('MODE_WRITE') || $bitMask === $reflection->getConstant('MODE_READ_WRITE');
            $actual = $stream->isWritable();
            $this->assertSame($expected, $actual);

            fclose($resource);
        }
    }

    public function testWrite()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);
        $content = self::CONTENT;
        $substr = 'c';
        $length = strlen($substr);

        $result = $stream->write($substr);
        $this->assertSame($length, $result);
        $content = substr_replace($content, $substr, 0, $length);
        $actual = file_get_contents(self::FILE_PATH);
        $this->assertSame($content, $actual);

        $offset = 4;
        $stream->seek($offset);
        $stream->write($substr);
        $content = substr_replace($content, $substr, $offset, strlen($substr));
        $actual = file_get_contents(self::FILE_PATH);
        $this->assertSame($content, $actual);
    }

    public function testIsReadable()
    {
        $reflection = new ReflectionClass(Stream::class);
        $modes = $reflection->getConstant('MODES');

        foreach ($modes as $mode => $bitMask) {
            $resource = $this->getResource($mode);
            $stream = new Stream($resource);

            $expected = $bitMask === $reflection->getConstant('MODE_READ') || $bitMask === $reflection->getConstant('MODE_READ_WRITE');
            $actual = $stream->isReadable();
            $this->assertSame($expected, $actual);

            fclose($resource);
        }
    }

    public function testRead()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $limit = 2;
        $actual = $stream->read($limit);
        $expected = substr(self::CONTENT, 0, $limit);
        $this->assertSame($expected, $actual);

        $length = strlen(self::CONTENT) + 1; // Length can be more than content length
        $actual = $stream->read($length);
        $expected = substr(self::CONTENT, 2, $length);
        $this->assertSame($expected, $actual);
    }

    public function testGetContents()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);
        $length = strlen(self::CONTENT);

        $actual = $stream->getContents();
        $expected = self::CONTENT;
        $this->assertSame($expected, $actual);

        $offset = 1;
        $stream->seek($length - $offset);
        $actual = $stream->getContents();
        $expected = substr(self::CONTENT, -$offset);
        $this->assertSame($expected, $actual);
    }

    public function testGetMetadata()
    {
        $resource = $this->getResource();
        $stream = new Stream($resource);

        $actual = $stream->getMetadata();
        $this->assertIsArray($actual);
        $this->assertArrayHasKey('mode', $actual);

        $actual = $stream->getMetadata('mode');
        $this->assertSame(self::MODE, $actual);

        $actual = $stream->getMetadata('undefined');
        $this->assertNull($actual);
    }

    /**
     * @param string $mode
     * @return resource|false
     */
    protected function getResource(string $mode = self::MODE)
    {
        $hasX = isset($mode[0]) && $mode[0] == 'x';

        if ($hasX) {
            @unlink(self::FILE_PATH);
        } else {
            @file_put_contents(self::FILE_PATH, self::CONTENT);
        }

        return fopen(self::FILE_PATH, $mode);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(self::FILE_PATH);
    }
}
