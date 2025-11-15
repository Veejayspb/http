<?php

declare(strict_types=1);

namespace Veejay\Http;

use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected const PORT_MIN = 0;
    protected const PORT_MAX = 65535;

    protected const RESERVED_CHARS_PATH = "!$&'()*+,;=-_.~:@/";
    protected const RESERVED_CHARS_QUERY = self::RESERVED_CHARS_PATH . '?';
    protected const RESERVED_CHARS_FRAGMENT = self::RESERVED_CHARS_QUERY;

    /**
     * @var string
     * @example http|https
     */
    protected string $scheme = '';

    /**
     * @var string
     * @example user
     */
    protected string $user = '';

    /**
     * @var string|null
     * @example pass
     */
    protected ?string $password = null;

    /**
     * @var string
     * @example domain.ru
     */
    protected string $host = '';

    /**
     * @var int|null
     * @example 80|443
     */
    protected ?int $port = null;

    /**
     * @var string
     * @example index.php
     */
    protected string $path = '';

    /**
     * @var string
     * @example a=1&b=2
     */
    protected string $query = '';

    /**
     * @var string
     * @example fragment
     */
    protected string $fragment = '';

    /**
     * @param string $uri
     * @throws InvalidArgumentException|LogicException
     */
    public function __construct(string $uri = '')
    {
        if ($uri == '') {
            return;
        }

        $parts = parse_url($uri);

        if ($parts === false) {
            throw new InvalidArgumentException('The source URI string appears to be malformed.');
        }

        if (array_key_exists('scheme', $parts)) {
            $this->setScheme($parts['scheme']);
        }

        if (array_key_exists('user', $parts)) {
            $this->setUserInfo($parts['user'], $parts['pass'] ?? null);
        }

        if (array_key_exists('host', $parts)) {
            $this->setHost($parts['host']);
        }

        if (array_key_exists('port', $parts)) {
            $this->setPort($parts['port']);
        }

        if (array_key_exists('path', $parts)) {
            $this->setPath($parts['path']);
        }

        if (array_key_exists('query', $parts)) {
            $this->setQuery($parts['query']);
        }

        if (array_key_exists('fragment', $parts)) {
            $this->setFragment($parts['fragment']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $uri = '';
        $authority = $this->getAuthority();
        $hasAuthority = $authority !== '';
        $path = $this->path;

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        if ($hasAuthority) {
            $uri .= '//' . $authority;
        }

        if ($path !== '') {
            $firstIsSlash = $path[0] === '/';
            $secondIsSlash = isset($path[1]) && $path[1] === '/';

            if ($hasAuthority && !$firstIsSlash) {
                $path = '/' . $path;
            } elseif (!$hasAuthority && $secondIsSlash) {
                $path = '/' . ltrim($path, '/');
            }

            $uri .= $path;
        }

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        $port = $this->getPort();
        $authority = $this->getHost();

        if ($authority === '') {
            return '';
        }

        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        if (!is_null($port)) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        $parts[] = $this->user;

        if (!is_null($this->password)) {
            $parts[] = $this->password;
        }

        return implode(':', $parts);
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme(string $scheme): UriInterface
    {
        $new = clone $this;
        $new->setScheme($scheme);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $new = clone $this;
        $new->setUserInfo($user, $password);
        return $new;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException|LogicException
     */
    public function withHost(string $host): UriInterface
    {
        $new = clone $this;
        $new->setHost($host);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort(?int $port): UriInterface
    {
        $new = clone $this;
        $new->setPort($port);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(string $path): UriInterface
    {
        $new = clone $this;
        $new->setPath($path);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery(string $query): UriInterface
    {
        $new = clone $this;
        $new->setQuery($query);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment(string $fragment): UriInterface
    {
        $new = clone $this;
        $new->setFragment($fragment);
        return $new;
    }

    /**
     * Scheme setter.
     * @param string $scheme
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setScheme(string $scheme): void
    {
        if (!preg_match('/^[a-z][a-z0-9+.-]*$/i', $scheme)) {
            throw new InvalidArgumentException(sprintf('Invalid scheme: %s', $scheme));
        }

        $this->scheme = strtolower($scheme);
    }

    /**
     * User info setter.
     * @param string $user
     * @param string|null $password
     * @return void
     */
    protected function setUserInfo(string $user, ?string $password = null): void
    {
        $this->user = rawurlencode($user);
        $this->password = is_null($password) ? null : rawurlencode($password);
    }

    /**
     * Host setter.
     * @param string $host
     * @return void
     * @throws LogicException|InvalidArgumentException
     */
    protected function setHost(string $host): void
    {
        // Convert to Punycode if host has non-ASCII symbols
        if (!mb_check_encoding($host, 'ASCII')) {
            if (!extension_loaded('intl')) {
                throw new LogicException('ext-intl required for IDN support');
            }

            $host = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if ($host === false) {
                throw new InvalidArgumentException('Invalid IDN host');
            }
        }

        $this->host = strtolower($host);
    }

    /**
     * Port setter.
     * @param int|null $port
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setPort(?int $port): void
    {
        if (is_int($port) && ($port < self::PORT_MIN || self::PORT_MAX < $port)) {
            throw new InvalidArgumentException(sprintf('Invalid port: %d. Must be between %d and %d', $port, self::PORT_MIN, self::PORT_MAX));
        }

        $this->port = $port;
    }

    /**
     * Path setter.
     * @param string $path
     * @return void
     */
    protected function setPath(string $path): void
    {
        $reserved = preg_quote(self::RESERVED_CHARS_PATH, '/');
        $pattern = "/[^A-Za-z0-9$reserved]/";
        $callback = fn(array $matches) => rawurlencode($matches[0]);
        $path = preg_replace_callback($pattern, $callback, $path); /* @var string $path */
        $this->path = $path;
    }

    /**
     * Query setter.
     * @param string $query
     * @return void
     */
    protected function setQuery(string $query): void
    {
        if ($query !== '' && $query[0] === '?') {
            $query = substr($query, 1);
        }

        $reserved = preg_quote(self::RESERVED_CHARS_QUERY, '/');
        $pattern = "/[^A-Za-z0-9$reserved]/";
        $callback = fn(array $matches) => rawurlencode($matches[0]);
        $query = preg_replace_callback($pattern, $callback, $query); /* @var string $query */
        $this->query = $query;
    }

    /**
     * Fragment setter.
     * @param string $fragment
     * @return void
     */
    protected function setFragment(string $fragment): void
    {
        if ($fragment !== '' && $fragment[0] === '#') {
            $fragment = substr($fragment, 1);
        }

        $reserved = preg_quote(self::RESERVED_CHARS_FRAGMENT, '/');
        $pattern = "/[^A-Za-z0-9$reserved]/";
        $callback = fn(array $matches) => rawurlencode($matches[0]);
        $fragment = preg_replace_callback($pattern, $callback, $fragment); /* @var string $fragment */
        $this->fragment = $fragment;
    }
}
