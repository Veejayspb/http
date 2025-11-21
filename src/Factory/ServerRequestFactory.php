<?php

declare(strict_types=1);

namespace Veejay\Http\Factory;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Veejay\Http\AbstractMessage;
use Veejay\Http\ServerRequest;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, AbstractMessage::DEFAULT_PROTOCOL_VERSION, [], null, $serverParams);
    }
}
