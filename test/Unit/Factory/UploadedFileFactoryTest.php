<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Psr\Http\Message\StreamInterface;
use Test\TestCase;
use Veejay\Http\Factory\UploadedFileFactory;
use Veejay\Http\Stream;

final class UploadedFileFactoryTest extends TestCase
{
    protected const CONTENT = 'Some file content';

    public function testCreateUploadedFileWithSize()
    {
        $size = 123;
        $stream = $this->getStream();
        $factory = new UploadedFileFactory;
        $file = $factory->createUploadedFile($stream, $size);

        $this->assertSame($size, $file->getSize());
    }

    public function testCreateUploadedFileWithoutSize()
    {
        $size = null;
        $stream = $this->getStream();
        $factory = new UploadedFileFactory;
        $file = $factory->createUploadedFile($stream, $size);

        $expected = strlen(self::CONTENT);
        $this->assertSame($expected, $file->getSize());
    }

    /**
     * @return StreamInterface
     */
    protected function getStream()
    {
        $resource = fopen('php://temp', 'r+');

        fwrite($resource, self::CONTENT);
        rewind($resource);

        return new Stream($resource);
    }
}
