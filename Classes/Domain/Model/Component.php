<?php

declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

class Component
{
    protected string $namespace;
    protected string $package;
    protected string $name;
    protected string $file;

    protected array $data = [];
    protected ?string $class = null;
    protected ?string $prefix = null;

    public function __construct(
        string $fullNamespace,
        string $package,
        string $name,
        string $file
    ) {
        $this->namespace = $fullNamespace;
        $this->package = $package;
        $this->name = $name;
        $this->file = $file;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return dirname($this->file);
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function __toString(): string
    {
        return $this->file;
    }
}
