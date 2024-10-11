<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Link;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Data Structure to provide information extracted from a Typolink string
 * in a structured matter.
 */
class Typolink extends Link implements ConstructibleFromInteger, ConstructibleFromArray
{
    /**
     * Data interpretation of the provided TYPO3 uri.
     *
     * @see LinkService::resolve()
     */
    protected array $originalLink = [];

    /**
     * Link target window of the Typolink
     * e. g. _blank.
     */
    protected string $target = '';

    /**
     * Additional CSS classes for the html element.
     */
    protected string $class = '';

    /**
     * Title attribute for the html element.
     */
    protected string $title = '';

    /**
     * Creates a Typolink data structure from a Typolink string.
     *
     * @param string $typolink e. g. t3://page?uid=123 _blank - "Link title"
     */
    public function __construct(string $typolink)
    {
        // Extract Typolink array configuration from string
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typolinkConfiguration = $typoLinkCodec->decode($typolink);

        // Analyze structure of provided TYPO3 uri
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $uriStructure = $linkService->resolve($typolinkConfiguration['url']);

        // Generate general purpose uri (https://) from TYPO3 uri (t3://)
        // Could also be a mailto or tel uri
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $uri = $cObj->typoLink_URL([
            'parameter' => $typolinkConfiguration['url'],
            'additionalParams' => $typolinkConfiguration['additionalParams'],
        ]);

        $this
            ->setUri($uri)
            ->setOriginalLink($uriStructure)
            ->setTarget($typolinkConfiguration['target'])
            ->setClass($typolinkConfiguration['class'])
            ->setTitle($typolinkConfiguration['title']);
    }

    /**
     * Creates a Typolink data structure from a page uid.
     */
    public static function fromInteger(int $pageUid): self
    {
        return new static((string) $pageUid);
    }

    /**
     * Creates a Typolink data structure from an array.
     *
     * Possible array keys are:
     * - uri (required)
     * - target
     * - class
     * - title
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $typolinkData): self
    {
        if (!isset($typolinkData['uri'])) {
            throw new InvalidArgumentException(
                'At least an URI has to be provided to be able to create a Typolink object.',
                1564488090
            );
        }

        $instance = new static((string) $typolinkData['uri']);

        if (isset($typolinkData['target'])) {
            $instance->setTarget((string) $typolinkData['target']);
        }

        if (isset($typolinkData['class'])) {
            $instance->setClass((string) $typolinkData['class']);
        }

        if (isset($typolinkData['title'])) {
            $instance->setTitle((string) $typolinkData['title']);
        }

        return $instance;
    }

    public function setOriginalLink(array $originalLink): self
    {
        $this->originalLink = $originalLink;
        return $this;
    }

    public function getOriginalLink(): array
    {
        return $this->originalLink;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
