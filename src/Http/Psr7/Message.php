<?php

namespace SolaPhp\Http\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    private string $protocol = '1.1';

    private array $headers = [];

    private ?StreamInterface $stream = null;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($this->protocol === $version) {
            return $this;
        }
        $that = clone $this;
        $that->protocol = $version;
        return $that;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        $name = strtolower($name);
        if (!$this->hasHeader($name)) {
            return [];
        }
        return $this->headers[$name];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $name = $this->validateHeaderName($name);
        $value = $this->validateHeaderValue($value);

        $that = clone $this;
        if ($that->hasHeader($name)) {
            unset($that->headers[$name]);
        }
        $that->headers[$name] = $value;

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $name = $this->validateHeaderName($name);
        $value = $this->validateHeaderValue($value);

        $that = clone $this;
        if ($that->hasHeader($name)) {
            $that->headers[$name] = array_merge($that->headers[$name], $value);
        } else {
            $that->headers[$name] = $value;
        }

        return $that;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $name = $this->validateHeaderName($name);
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        if (is_null($this->stream)) {
            $this->stream = Stream::create('');
        }
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $that = clone $this;
        $that->stream = $body;
        return $that;
    }

    /**
     * Validate Header name.
     *
     * @param string $name
     * @return string
     *
     * @throws \InvalidArgumentException When the name is empty
     */
    protected function validateHeaderName(string $name): string
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Header name can not be empty");
        }

        return strtolower(trim($name));
    }

    /**
     * Validate Header value.
     *
     * @param string $name
     * @return string
     *
     * @throws \InvalidArgumentException When the value is an empty string or empty array of strings
     */
    protected function validateHeaderValue(string|array $value): array
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Header value can not be empty");
        }

        if (is_string($value)) {
            $value = [$value];
        }

        array_map('trim', $value);

        return $value;
    }
}
