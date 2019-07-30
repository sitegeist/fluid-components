<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ConstructibleFromString;

/**
 * Data Structure to provide information and fragments of link
 * in a structured matter
 */
class Link implements ConstructibleFromString
{
    /**
     * Target URI of the link
     *
     * @var string
     */
    protected $uri = '';

    /**
     * URI scheme, e. g. https://
     *
     * @var string|null
     */
    protected $scheme;

    /**
     * Host name, e. g. domain.tld
     *
     * @var string|null
     */
    protected $host;

    /**
     * Port number, e. g. 8080
     *
     * @var int|null
     */
    protected $port;

    /**
     * HTTP Basic Auth User
     *
     * @var string|null
     */
    protected $user;

    /**
     * HTTP Basic Auth Password
     *
     * @var string|null
     */
    protected $pass;

    /**
     * Path part of the URI, e. g. /my/path/file.html
     *
     * @var string|null
     */
    protected $path;

    /**
     * Query string of the URI (without the leading ?),
     * e. g. myParam=myValue&anotherParam=anotherValue
     *
     * @var string|null
     */
    protected $query;

    /**
     * Fragment/Anchor of the URI (without the leading #)
     *
     * @var string|null
     */
    protected $fragment;

    /**
     * Creates a link data structure from an URI
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->setUri($uri);
    }

    /**
     * Creates a link data structure from an URI
     *
     * @param string $uri
     * @return self
     */
    public static function fromString(string $uri): self
    {
        return new static($uri);
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        // Extract URI fragments
        $parsed = parse_url($uri);
        $this->scheme = $parsed['scheme'] ?? null;
        $this->host = $parsed['host'] ?? null;
        $this->port = $parsed['port'] ?? null;
        $this->user = $parsed['user'] ?? null;
        $this->pass = $parsed['pass'] ?? null;
        $this->path = $parsed['path'] ?? null;
        $this->query = $parsed['query'] ?? null;
        $this->fragment = $parsed['fragment'] ?? null;

        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Use URI as string representation of the object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUri();
    }
}
