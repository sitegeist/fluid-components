<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\NavigationItem;

/**
 * Data Structure to represent one item in a language navigation.
 */
class LanguageNavigationItem extends NavigationItem
{
    /**
     * Creates a language navigation item object.
     *
     * $title               Title of the navigation item
     * $link                Link of the navigation item
     * $current             Indicates whether the navigation item represents the current page (note that active will be true as well)
     * $active              Indicates whether the navigation item is part of the current rootline (parent page of the current page OR current page)
     * $available           Availability of translation for the specific page
     * $languageId          UID of the sys_language record
     * $locale              Locale definition for language
     * $twoLetterIsoCode    ISO code for language
     * $hreflang            Hreflang identifier for language
     * $direction           Directionality of text in the language (ltr or rtl)
     * $flag                Flag name for language
     * $data                additional data for this navigation item
     */
    public function __construct(
        protected string $title,
        protected ?Typolink $link = null,
        protected bool $current = false,
        protected bool $active = false,
        protected bool $available = false,
        protected ?int $languageId = null,
        protected ?string $locale = null,
        protected ?string $twoLetterIsoCode = null,
        protected ?string $hreflang = null,
        protected ?string $direction = null,
        protected ?string $flag = null,
        protected array $data = []
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
            ->setActive($active || $current)
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
     * Creates a navigation item object based on a TYPO3 language navigation item array.
     *
     * @param array $navigationItem respected properties that will become part of the data structure:
     *                              title, link, target, current, active, available, languageId, locale,
     *                              twoLetterIsoCode, hreflang, direction, flag, data
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

    public function setAvailable(bool $available): static
    {
        $this->available = $available;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): static
    {
        $this->languageId = $languageId;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTwoLetterIsoCode(): string
    {
        return $this->twoLetterIsoCode;
    }

    public function setTwoLetterIsoCode(string $twoLetterIsoCode): static
    {
        $this->twoLetterIsoCode = $twoLetterIsoCode;
        return $this;
    }

    public function getHreflang(): string
    {
        return $this->hreflang;
    }

    public function setHreflang(string $hreflang): static
    {
        $this->hreflang = $hreflang;
        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): static
    {
        $this->flag = $flag;
        return $this;
    }
}
