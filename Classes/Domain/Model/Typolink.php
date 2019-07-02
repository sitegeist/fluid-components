<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Link;
use SMS\FluidComponents\Interfaces\ConstructibleFromInteger;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;
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
    protected $additionalParams;

    public function __construct(string $typolink)
    {
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typolinkConfiguration = $typoLinkCodec->decode($typolink);

        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urn = $linkService->resolve($typolinkConfiguration['url']);

        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $url = $cObj->typoLink_URL([
            'parameter' => $typolinkConfiguration['url']
        ]);

        $this
            ->setUri($url)
            ->setOriginalLink($urn)
            ->setTarget($typolinkConfiguration['target'])
            ->setClass($typolinkConfiguration['class'])
            ->setTitle($typolinkConfiguration['title'])
            ->setAdditionalParams($typolinkConfiguration['additionalParams']);
    }

    public static function fromInteger(int $pageUid): self
    {
        return new static((string) $pageUid);
    }

    public static function fromArray(array $typolinkData): self
    {
        $instance = new static($typolinkData['uri']);
        $instance->setTarget($typolinkData['target'] ?? '');
        $instance->setClass($typolinkData['class'] ?? '');
        $instance->setTitle($typolinkData['title'] ?? '');
        $instance->setAdditionalParams($typolinkData['additionalParams'] ?? '');
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

    public function setAdditionalParams(string $additionalParams): self
    {
        $this->additionalParams = $additionalParams;
        return $this;
    }

    public function getAdditionalParams(): string
    {
        return $this->additionalParams;
    }
}
