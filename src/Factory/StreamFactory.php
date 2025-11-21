<?php

declare(strict_types=1);

namespace Veejay\Http\Factory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Veejay\Http\Stream;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');

        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);

        if ($resource === false) {
            throw new RuntimeException(sprintf('Could not open file: %s', $filename));
        }

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
