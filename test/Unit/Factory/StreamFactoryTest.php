<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Test\TestCase;
use Veejay\Http\Factory\StreamFactory;

final class StreamFactoryTest extends TestCase
{
    protected const FILE_PATH = __DIR__ . '/../../Temp/temp.txt';
    protected const CONTENT = 'Some file content';

    public function testCreateStream()
    {
        $factory = new StreamFactory;
        $stream = $factory->createStream(self::CONTENT);

        $this->assertSame(self::CONTENT, $stream->getContents());
    }

    public function testCreateStreamFromFile()
    {
        $factory = new StreamFactory;
        $stream = $factory->createStreamFromFile(self::FILE_PATH);

        $this->assertSame(self::CONTENT, $stream->getContents());
    }

    public function testCreateStreamFromResource()
    {
        $resource = fopen(self::FILE_PATH, 'r+');;
        $factory = new StreamFactory;
        $stream = $factory->createStreamFromResource($resource);

        $this->assertSame(self::CONTENT, $stream->getContents());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(self::FILE_PATH, self::CONTENT);
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
