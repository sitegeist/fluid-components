<?php

namespace SMS\FluidComponents\Utility;

use SMS\FluidComponents\Utility\ComponentSettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ComponentContext
{
    protected $settings;
    protected $languageKey;
    protected $alternativeLanguageKeys;

    public function __construct(ComponentSettings $settings = null)
    {
        $this->settings = $settings ?? GeneralUtility::makeInstance(ComponentSettings::class);
    }

    public function applyDefaultsFromParentContext(ComponentContext $parentContext): self
    {
        if ($this->getLanguageKey() === null) {
            $this->setLanguageKey($parentContext->getLanguageKey());
        }
        if ($this->getAlternativeLanguageKeys() === null) {
            $this->setAlternativeLanguageKeys($parentContext->getAlternativeLanguageKeys());
        }
        return $this;
    }

    public function getSettings(): ComponentSettings
    {
        return $this->settings;
    }

    public function getLanguageKey(): ?string
    {
        return $this->languageKey;
    }

    public function setLanguageKey(?string $languageKey): self
    {
        $this->languageKey = $languageKey;
        return $this;
    }

    public function getAlternativeLanguageKeys(): ?array
    {
        return $this->alternativeLanguageKeys;
    }

    public function setAlternativeLanguageKeys(?array $alternativeLanguageKeys): self
    {
        $this->alternativeLanguageKeys = $alternativeLanguageKeys;
        return $this;
    }

    public function reset(): self
    {
        $this->setLanguageKey(null);
        $this->setAlternativeLanguageKeys(null);
        return $this;
    }
}
