<?php

namespace SolaPhp\Http\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private const SCHEMES = [
        'http' => 80,
        'https' => 443,
    ];

    private string $scheme;
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $userInfo;
    private string $path;
    private string $query;
    private string $fragment;

    public function __construct(public string $uri = '')
    {
        if ('' === $uri) {
            return;
        }

        $urlParts = parse_url($uri);
        if (false === $urlParts) {
            throw new \InvalidArgumentException("Uri Invalid");
        }

        $this->scheme = $urlParts['scheme'] ?? null;
        $this->host = $urlParts['host'] ?? null;
        $this->port = (int) $urlParts['port'] ?? null;
        $this->user = $urlParts['user'] ?? null;
        $this->password = $urlParts['pass'] ?? null;
        $this->path = $urlParts['path'] ?? null;
        $this->query = $urlParts['query'] ?? null;
        $this->fragment = $urlParts['fragment'] ?? null;

        if (isset($this->user) && isset($this->password)) {
            $this->userInfo = $this->user . ':' . $this->password;
        }
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @todo Implement default port
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(string $scheme): UriInterface
    {
        $that = clone $this;
        $that->scheme = $scheme;
        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $that = clone $this;
        $that->user = $user;
        $that->password = $password;

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withHost(string $host): UriInterface
    {
        if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException("Invalid Hostname");
        }

        $that = clone $this;
        $that->host = $host;
        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withPort(?int $port): UriInterface
    {
        if (!filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 0xFFFF]])) {
            throw new \InvalidArgumentException("Invalid Port $port . Port should be between 0 and 65535");
        }

        $that = clone $this;
        $that->port = $port;
        return $that;
    }

    /**
     * @todo Validate path
     */
    public function withPath(string $path): UriInterface
    {
        $that = clone $this;
        $that->path = $path;
        return $that;
    }

    /**
     * @todo validate query
     *
     * @inheritDoc
     */
    public function withQuery(string $query): UriInterface
    {
        $that = clone $this;
        $that->query = $query;
        return $that;
    }

    /**
     * @todo Validate Fragment
     * @inheritDoc
     */
    public function withFragment(string $fragment): UriInterface
    {
        $that = clone $this;
        $that->fragment = $fragment;
        return $that;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $uri = '';
        if ('' !== $this->scheme) {
            $uri .= $this->scheme . ':';
        }
        $authority = $this->getAuthority();
        if ('' !== $authority) {
            $uri .= '//' . $authority;
        }

        $path = $this->path;
        if ('' !== $path) {
            if ('/' !== $path[0]) {
                if ('' !== $authority) {
                    $path = '/' . $path;
                }
            } elseif (isset($path[1]) && '/' === $path[1]) {
                if ('' === $authority) {
                    $path = '/' . \ltrim($path, '/');
                }
            }

            $uri .= $path;
        }

        if ('' !== $this->query) {
            $uri .= '?' . $this->query;
        }

        if ('' !== $this->fragment) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
