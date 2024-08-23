<?php
declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Interfaces\ConstructibleFromString;
use SMS\FluidComponents\Exception\InvalidFileArrayException;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Exception\InvalidRemoteFileException;
use SMS\FluidComponents\Interfaces\ConstructibleFromExtbaseFile;
use SMS\FluidComponents\Exception\FileReferenceNotFoundException;
use SMS\FluidComponents\Interfaces\ConstructibleFromFileInterface;

/**
 * Generic data structures to pass files from various sources
 * (FAL, file path, external file uri) to components
 * by using a clear API.
 */
abstract class File implements
    ConstructibleFromString,
    ConstructibleFromInteger,
    ConstructibleFromArray,
    ConstructibleFromFileInterface,
    ConstructibleFromExtbaseFile
{
    /**
     * Type of file to differentiate implementations in Fluid templates
     */
    protected string $type = 'File';

    /**
     * Title of the file
     */
    protected ?string $title = null;

    /**
     * Description of the file
     */
    protected ?string $description = null;

    /**
     * Properties of the file
     */
    protected ?array $properties = null;

    /**
     * Should return the public URL of the file to be used in an img tag
     */
    abstract public function getPublicUrl(): string;

    /**
     * Creates a file object based on a static file (local or remote)
     */
    public static function fromString(string $value): ?self
    {
        if ($value === '') {
            return null;
        }

        try {
            return new RemoteFile($value);
        } catch (InvalidRemoteFileException) {
            $file = GeneralUtility::makeInstance(ResourceFactory::class)->retrieveFileOrFolderObject($value);
            return ($file) ? new FalFile($file) : null;
        }
    }

    /**
     * Creates a file object based on a FAL file uid
     */
    public static function fromInteger(int $value): self
    {
        return static::fromFileUid($value);
    }

    /**
     * Creates a file object based on data passed by array. There are numerous
     * array structures that create a valid file object:
     *
     * FAL file object:
     *     [ "fileObject" => $myFileObject ]
     *
     * FAL file uid:
     *
     *     [ "fileUid" => 123 ]
     *
     * FAL file reference uid:
     *
     *     [ "fileReferenceUid" => 456 ]
     *
     * FAL file reference data:
     *
     *     [
     *         "fileReference" => [
     *             "tableName" => "pages",
     *             "fieldName" => "media",
     *             "uid" => 123,
     *             "counter" => 0
     *         ]
     *     ]
     *
     * Static file path:
     *
     *     [ "file" => "EXT:my_extension/Resources/Public/Files/MyFile.txt" ]
     *
     *     [
     *         "resource" => [
     *             "extensionName" => "myExtension",
     *             "path" => "Files/MyFile.txt"
     *         ]
     *     ]
     *
     *     [
     *         "resource" => [
     *             "extensionKey" => "my_extension",
     *             "path" => "Files/MyFile.txt"
     *         ]
     *     ]
     *
     * Static file uri:
     *
     *     [ "file" => "https://example.com/MyFile.txt" ]
     *
     * @throws InvalidArgumentException|FileReferenceNotFoundException
     */
    public static function fromArray(array $value): ?self
    {
        // Create an imafe from an existing FAL object
        if (isset($value['fileObject'])) {
            $file = static::fromFileInterface($value['fileObject']);
            // Create a file from file uid
        } elseif (isset($value['fileUid'])) {
            $file = static::fromFileUid((int) $value['fileUid']);
            // Create a file from file reference uid
        } elseif (isset($value['fileReferenceUid'])) {
            $file = static::fromFileReferenceUid((int) $value['fileReferenceUid']);
            // Create a file from file reference data (table, field, uid, counter)
        } elseif (isset($value['fileReference']) && is_array($value['fileReference'])) {
            $fileReference = $value['fileReference'];

            if (!isset($fileReference['tableName'])
                || !isset($fileReference['fieldName'])
                || !isset($fileReference['uid'])
            ) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid file reference description: %s',
                    print_r($fileReference, true)
                ), 1562916587);
            }

            $file = static::fromFileReference(
                (string) $fileReference['tableName'],
                (string) $fileReference['fieldName'],
                (int) $fileReference['uid'],
                (int) ($fileReference['counter'] ?? 0)
            );
            // Create a file from a static resource in an extension (Resources/Public/...)
        } elseif (isset($value['resource']) && is_array($value['resource'])) {
            $resource = $value['resource'];

            if (!isset($resource['path'])) {
                throw new InvalidArgumentException(sprintf(
                    'Missing path for file resource: %s',
                    print_r($resource, true)
                ), 1564492445);
            }

            if (isset($resource['extensionKey'])) {
                $extensionKey = $resource['extensionKey'];
            } elseif (isset($resource['extensionName'])) {
                $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored(
                    $resource['extensionName']
                );
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Missing extension key or extension name for file resource: %s',
                    print_r($resource, true)
                ), 1564492446);
            }

            $file = static::fromExtensionResource($extensionKey, $resource['path']);
            // Create a file from a file path or uri
        } elseif (isset($value['file'])) {
            $file = static::fromString((string) $value['file']);
        } else {
            throw new InvalidFileArrayException(sprintf(
                'Invalid set of arguments for conversion to file instance: %s',
                print_r($value, true)
            ), 1562916607);
        }

        if (isset($value['title'])) {
            $file->setTitle($value['title']);
        }

        if (isset($value['description'])) {
            $file->setDescription($value['description']);
        }

        if (isset($value['properties'])) {
            $file->setProperties($value['properties']);
        }

        return $file;
    }

    /**
     * Creates a file object as a wrapper around an existing FAL object
     */
    public static function fromFileInterface(FileInterface $value): self
    {
        return new FalFile($value);
    }

    public static function fromExtbaseFile(FileReference $value): self
    {
        return static::fromFileInterface($value->getOriginalResource());
    }

    /**
     * Creates a file object based on a FAL file uid
     */
    public static function fromFileUid(int $fileUid): self
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $file = $fileRepository->findByUid($fileUid);
        return static::fromFileInterface($file);
    }

    /**
     * Creates a file object based on a FAL file reference uid
     */
    public static function fromFileReferenceUid(int $fileReferenceUid): self
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReference = $fileRepository->findFileReferenceByUid($fileReferenceUid);
        return static::fromFileInterface($fileReference);
    }

    /**
     * Creates a file object based on file reference data
     *
     * @param string $tableName  database table where the file is referenced
     * @param string $fieldName  database field name in which the file is referenced
     * @param int $uid       uid of the database record in which the file is referenced
     * @param int $counter   zero-based index of the file reference to use
     *                           (in case there are multiple)
     * @return self
     * @throws FileReferenceNotFoundException
     */
    public static function fromFileReference(
        string $tableName,
        string $fieldName,
        int $uid,
        int $counter = 0
    ): self {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileReferences = $fileRepository->findByRelation(
            (string) $tableName,
            (string) $fieldName,
            (int) $uid
        );

        if (!isset($fileReferences[$counter])) {
            throw new FileReferenceNotFoundException(sprintf(
                'File reference in %s.%s for uid %d at position %d could not be found.',
                $tableName,
                $fieldName,
                $uid,
                $counter
            ), 1564495695);
        }

        return static::fromFileInterface($fileReferences[$counter]);
    }

    /**
     * Creates a file object based on a static resource in an extension
     * (Resources/Public/...)
     *
     * @see \TYPO3\CMS\Fluid\ViewHelpers\Uri\ResourceViewHelper
     */
    public static function fromExtensionResource(string $extensionKey, string $path): ?self
    {
        return static::fromString('EXT:' . $extensionKey . '/Resources/Public/' . $path);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setProperties(?array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Use public url of file as string representation of file objects
     */
    public function __toString(): string
    {
        return $this->getPublicUrl();
    }
}
