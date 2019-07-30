<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Link;
use SMS\FluidComponents\Exception\InvalidArgumentException;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class Typolink extends Link implements ConstructibleFromInteger, ConstructibleFromArray
{
    protected $originalLink;
    protected $target;
    protected $class;
    protected $title;

    public function __construct(string $typolink)
    {
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typolinkConfiguration = $typoLinkCodec->decode($typolink);

        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urn = $linkService->resolve($typolinkConfiguration['url']);

        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $url = $cObj->typoLink_URL([
            'parameter' => $typolinkConfiguration['url'],
            'additionalParams' => $typolinkConfiguration['additionalParams']
        ]);

        $this
            ->setUri($url)
            ->setOriginalLink($urn)
            ->setTarget($typolinkConfiguration['target'])
            ->setClass($typolinkConfiguration['class'])
            ->setTitle($typolinkConfiguration['title']);
    }

    public static function fromInteger(int $pageUid): self
    {
        return new static((string) $pageUid);
    }

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
