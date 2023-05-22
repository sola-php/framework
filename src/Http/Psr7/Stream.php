<?php

namespace SolaPhp\Http\Psr7;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * Available modes for reading
     * @see Table 1 in fopen https://www.php.net/manual/en/function.fopen.php
     */
    private const READABLE_MODES = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];

    /**
     * Available modes for writing
     * @see Table 1 in fopen https://www.php.net/manual/en/function.fopen.php
     */
    private const WRITABLE_MODES = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];

    /** @var resource */
    private $stream;

    /** @var bool */
    private $seekable;

    /** @var bool */
    private $readable;

    /** @var bool */
    private $writable;

    /** @var array|mixed|void|bool|null */
    private $uri;

    /** @var int|null */
    private $size;

    public function __construct($body)
    {
        if (!\is_resource($body)) {
            throw new \InvalidArgumentException('$body must be resource');
        }

        $this->stream = $body;
        $meta = $this->getMetadata();
        $this->seekable = $meta['seekable'] && 0 === \fseek($this->stream, 0, \SEEK_CUR);
        $this->readable = \in_array($meta['mode'], self::READABLE_MODES);
        $this->writable = \in_array($meta['mode'], self::WRITABLE_MODES);
        $this->uri = $meta['uri'];
    }

    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface $body
     *
     * @throws \InvalidArgumentException
     */
    public static function create($body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (\is_string($body)) {
            $resource = \fopen('php://memory', 'r+');
            \fwrite($resource, $body);
            \fseek($resource, 0);
            $body = $resource;
        }

        if (!\is_resource($body)) {
            throw new \InvalidArgumentException('First argument to Stream::create() must be a string, resource or StreamInterface');
        }

        return new self($body);
    }

    /**
     * Close the stream when the object is destroyed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }


    /**
     * @inheritDoc
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        if ($this->size) {
            return $this->size;
        }

        if (!$this->stream) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if (isset($this->uri)) {
            \clearstatcache(true, $this->uri);
        }

        $stats = \fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (false === $result = @\ftell($this->stream)) {
            throw new \RuntimeException('Unable to determine stream position: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        return !$this->stream || \feof($this->stream);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * @inheritDoc
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->stream) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (false === fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset . ': ' . (\error_get_last()['message'] ?? ''));
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * @inheritDoc
     */
    public function write(string $string): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;

        if (false === $result = \fwrite($this->stream, $string)) {
            throw new \RuntimeException('Unable to write to stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * @inheritDoc
     */
    public function read(int $length): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        if (false === $result = \fread($this->stream, $length)) {
            throw new \RuntimeException('Unable to read from stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (false === $contents = @\stream_get_contents($this->stream)) {
            throw new \RuntimeException('Unable to read stream contents: ' . (\error_get_last()['message'] ?? ''));
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(?string $key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream);

        if (!$key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}
