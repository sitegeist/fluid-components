<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Navigation;
use SMS\FluidComponents\Domain\Model\Typolink;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;

/**
 * Data Structure to represent one item in a navigation.
 */
class NavigationItem implements ConstructibleFromArray
{
    /**
     * Creates a navigation item object.
     *
     * $title       Title of the navigation item
     * $link        Link of the navigation item
     * $current     Indicates whether the navigation item represents the current page (note that active will be true as well)
     * $active      Indicates whether the navigation item is part of the current rootline (parent page of the current page OR current page)
     * $spacer      Indicates whether the navigation item is a spacer item without real functionality
     * $children    Submenu of the navigation item
     * $data        Raw data of the navigation item, e. g. the pages record
     */
    public function __construct(
        protected string $title,
        protected ?Typolink $link = null,
        protected bool $current = false,
        protected bool $active = false,
        protected bool $spacer = false,
        protected ?Navigation $children = null,
        protected array $data = []
    ) {
        // Ensure valid data structures for optional values
        if (!$link) {
            $link = new Typolink('');
        }
        if (!$children) {
            $children = new Navigation([]);
        }

        $this
            ->setTitle($title)
            ->setLink($link)
            ->setCurrent($current)
            ->setActive($active || $current)
            ->setSpacer($spacer)
            ->setChildren($children)
            ->setData($data);
    }

    /**
     * Creates a navigation item object based on a TYPO3 navigation item array.
     *
     * @param array $navigationItem respected properties that will become part of the data structure:
     *                              title, link, target, current, active, spacer, children, data
     */
    public static function fromArray(array $navigationItem): self
    {
        // Convert link and sub navigation to the appropriate data structure
        if (isset($navigationItem['link'])) {
            if (!$navigationItem['link'] instanceof Typolink) {
                $navigationItem['link'] = new Typolink($navigationItem['link']);
            }
            if (isset($navigationItem['target'])) {
                $navigationItem['link']->setTarget($navigationItem['target']);
            }
        }
        if (isset($navigationItem['children']) && !$navigationItem['children'] instanceof Navigation) {
            $navigationItem['children'] = new Navigation($navigationItem['children']);
        }

        return new static(
            $navigationItem['title'] ?? '',
            $navigationItem['link'] ?? null,
            (bool) ($navigationItem['current'] ?? false),
            (bool) ($navigationItem['active'] ?? $navigationItem['current'] ?? false),
            (bool) ($navigationItem['spacer'] ?? false),
            $navigationItem['children'] ?? null,
            $navigationItem['data'] ?? []
        );
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getLink(): ?Typolink
    {
        return $this->link;
    }

    public function setLink(Typolink $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getTarget(): string
    {
        return $this->link->getTarget();
    }

    public function setTarget(string $target): static
    {
        $this->link->setTarget($target);
        return $this;
    }

    public function getCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): static
    {
        $this->current = $current;
        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getSpacer(): bool
    {
        return $this->spacer;
    }

    public function setSpacer(bool $spacer): static
    {
        $this->spacer = $spacer;
        return $this;
    }

    public function getChildren(): Navigation
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    public function setChildren(Navigation $children): static
    {
        $this->children = $children;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }
}
