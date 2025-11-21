<?php

declare(strict_types=1);

namespace Veejay\Http\Factory;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Veejay\Http\Request;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
