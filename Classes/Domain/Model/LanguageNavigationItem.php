<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\NavigationItem;

/**
 * Data Structure to represent one item in a language navigation
 */
class LanguageNavigationItem extends NavigationItem
{
    /**
     * Availability of translation for the specific page
     */
    protected bool $available = false;

    /**
     * UID of the sys_language record
     */
    protected int $languageId;

    /**
     * Locale definition for language
     */
    protected string $locale;

    /**
     * ISO code for language
     */
    protected string $twoLetterIsoCode;

    /**
     * Hreflang identifier for language
     */
    protected string $hreflang;

    /**
     * Directionality of text in the language (ltr or rtl)
     */
    protected string $direction;

    /**
     * Flag name for language
     */
    protected string $flag;

    /**
     * Creates a navigation item object
     *
     * @param string $title             title of the item
     * @param Typolink $link            link of the item
     * @param bool $current          true if item represents the current page
     * @param bool $active           true if item is part of current rootline
     * @param bool $available        true if current page is translated to language
     * @param int $languageId           UID of the sys_language record
     * @param string $locale            Locale definition for language
     * @param string $twoLetterIsoCode  ISO code for language
     * @param string $hreflang          Hreflang identifier for language
     * @param string $direction         Directionality of text in the language (ltr or rtl)
     * @param string $flag              Flag name for language
     * @param array $data               additional data for this navigation item
     */
    public function __construct(
        string $title,
        Typolink $link = null,
        bool $current = false,
        bool $active = null,
        bool $available = false,
        int $languageId = null,
        string $locale = null,
        string $twoLetterIsoCode = null,
        string $hreflang = null,
        string $direction = null,
        string $flag = null,
        array $data = []
    ) {
        // Ensure valid data structures for optional values
        $this->setChildren(new Navigation([]));
        if (!$link) {
            $link = new Typolink('');
        }

        $this
            ->setTitle($title)
            ->setLink($link)
            ->setCurrent($current)
            ->setActive($active ?? $current ?? false)
            ->setAvailable($available)
            ->setLanguageId($languageId)
            ->setLocale($locale)
            ->setTwoLetterIsoCode($twoLetterIsoCode)
            ->setHreflang($hreflang)
            ->setDirection($direction)
            ->setFlag($flag)
            ->setData($data);
    }

    /**
     * Creates a navigation item object based on a TYPO3 language navigation item array
     *
     * @param array $navigationItem  respected properties that will become part of the data structure:
     *                               title, link, target, current, active, available, languageId, locale,
     *                               twoLetterIsoCode, hreflang, direction, flag, data
     */
    public static function fromArray(array $navigationItem): self
    {
        // Convert link to the appropriate data structure
        if (isset($navigationItem['link'])) {
            if (!$navigationItem['link'] instanceof Typolink) {
                $navigationItem['link'] = new Typolink($navigationItem['link']);
            }
            if (isset($navigationItem['target'])) {
                $navigationItem['link']->setTarget($navigationItem['target']);
            }
        }

        return new static(
            $navigationItem['title'] ?? '',
            $navigationItem['link'] ?? null,
            (bool) ($navigationItem['current'] ?? false),
            (bool) ($navigationItem['active'] ?? $navigationItem['current'] ?? false),
            (bool) ($navigationItem['available'] ?? false),
            $navigationItem['languageId'] ?? 0,
            $navigationItem['locale'] ?? '',
            $navigationItem['twoLetterIsoCode'] ?? '',
            $navigationItem['hreflang'] ?? '',
            $navigationItem['direction'] ?? '',
            $navigationItem['flag'] ?? '',
            $navigationItem['data'] ?? []
        );
    }

    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTwoLetterIsoCode(): string
    {
        return $this->twoLetterIsoCode;
    }

    public function setTwoLetterIsoCode(string $twoLetterIsoCode): self
    {
        $this->twoLetterIsoCode = $twoLetterIsoCode;
        return $this;
    }

    public function getHreflang(): string
    {
        return $this->hreflang;
    }

    public function setHreflang(string $hreflang): self
    {
        $this->hreflang = $hreflang;
        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): self
    {
        $this->flag = $flag;
        return $this;
    }
}
