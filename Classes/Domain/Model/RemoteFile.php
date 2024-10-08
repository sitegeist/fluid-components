<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Exception\InvalidRemoteFileException;

/**
 * Data structure for a remote file resource (URI) to be passed to a component.
 */
class RemoteFile extends File
{
    /**
     * Type of file to differentiate implementations in Fluid templates.
     */
    protected string $type = 'RemoteFile';

    /**
     * URI to the remote file.
     */
    protected string $uri = '';

    /**
     * Creates a file object for a remote resource.
     *
     * @throws InvalidRemoteFileException
     */
    public function __construct(string $uri)
    {
        if (!static::isRemoteUri($uri)) {
            throw new InvalidRemoteFileException(sprintf(
                'Invalid remote file uri provided: %s',
                $uri
            ), 1655976410);
        }

        $this->uri = $uri;
    }

    public function getPublicUrl(): string
    {
        return $this->uri;
    }

    /**
     * Checks if the provided uri is a valid remote uri.
     */
    protected static function isRemoteUri(string $uri): bool
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        return ($scheme && in_array(strtolower($scheme), ['http', 'https']));
    }
}
