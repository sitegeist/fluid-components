<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Interfaces\ConstructibleFromString;

class Link implements ConstructibleFromString
{
    protected $uri;

    protected $scheme;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $path;
    protected $query;
    protected $fragment;

    public function __construct(string $uri)
    {
        $this->setUri($uri);
    }

    public static function fromString(string $uri): self
    {
        return new static($uri);
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

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

    public function __toString(): string
    {
        return $this->getUri();
    }
}
