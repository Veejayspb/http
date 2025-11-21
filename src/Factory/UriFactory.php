<?php

declare(strict_types=1);

namespace Veejay\Http\Factory;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Veejay\Http\Uri;

class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
