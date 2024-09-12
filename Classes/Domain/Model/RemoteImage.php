<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\InvalidRemoteImageException;

/**
 * Data structure for a remote image resource (URI) to be passed to a component
 */
class RemoteImage extends Image
{
    /**
     * Type of image to differentiate implementations in Fluid templates
     */
    protected string $type = 'RemoteImage';

    /**
     * URI to the remote image
     */
    protected string $uri = '';

    /**
     * Creates an image object for a remote image resource
     *
     * @throws InvalidRemoteImageException
     */
    public function __construct(string $uri)
    {
        if (!static::isRemoteUri($uri)) {
            throw new InvalidRemoteImageException(sprintf(
                'Invalid remote image uri provided: %s',
                $uri
            ), 1564502104);
        }

        $this->uri = $uri;
    }

    public function getPublicUrl(): string
    {
        return $this->uri;
    }

    /**
     * Checks if the provided uri is a valid remote uri
     */
    protected static function isRemoteUri(string $uri): bool
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        return ($scheme && in_array(strtolower($scheme), ['http', 'https']));
    }
}
