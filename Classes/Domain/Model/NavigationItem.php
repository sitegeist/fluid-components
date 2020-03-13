<?php

namespace SMS\FluidComponents\Domain\Model;

use SMS\FluidComponents\Domain\Model\Typolink;
use SMS\FluidComponents\Domain\Model\Navigation;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;

/**
 * Data Structure to represent one item in a navigation
 */
class NavigationItem implements ConstructibleFromArray
{
    /**
     * Title of the navigation item
     *
     * @var string
     */
    protected $title;

    /**
     * Link of the navigation item
     *
     * @var Typolink
     */
    protected $link;

    /**
     * Indicates whether the navigation item is part of the current rootline
     * (parent page of the current page OR current page)
     *
     * @var boolean
     */
    protected $active = false;

    /**
     * Indicates whether the navigation item represents the current page
     * (note that active will be true as well)
     *
     * @var boolean
     */
    protected $current = false;

    /**
     * Indicates whether the navigation item is a spacer item without real
     * functionality
     *
     * @var boolean
     */
    protected $spacer = false;

    /**
     * Submenu of the navigation item
     *
     * @var Navigation
     */
    protected $children;

    /**
     * Raw data of the navigation item, e. g. the pages record
     *
     * @var array
     */
    protected $data = [];

    /**
     * Creates a navigation item object
     *
     * @param string $title         title of the item
     * @param Typolink $link        link of the item
     * @param boolean $current      true if item represents the current page
     * @param boolean $active       true if item is part of current rootline
     * @param boolean $spacer       true if item is a spacer item
     * @param Navigation $children  sub navigation
     * @param array $data           raw item data
     */
    public function __construct(
        string $title,
        Typolink $link = null,
        bool $current = false,
        bool $active = false,
        bool $spacer = false,
        Navigation $children = null,
        array $data = []
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
            ->setActive($active)
            ->setSpacer($spacer)
            ->setChildren($children)
            ->setData($data);
    }

    /**
     * Creates a navigation item object based on a TYPO3 navigation item array
     *
     * @param array $navigationItem  respected properties that will become part of the data structure:
     *                               title, link, target, current, active, spacer, children, data
     * @return self
     */
    public static function fromArray(array $navigationItem): self
    {
        // Convert link and sub navigation to the appropriate data structure
        if (isset($navigationItem['link']) && !$navigationItem['link'] instanceof Typolink) {
            $navigationItem['link'] = new Typolink($navigationItem['link']);
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
            $navigationItem['current'] ?? false,
            $navigationItem['active'] ?? false,
            $navigationItem['spacer'] ?? false,
            $navigationItem['children'] ?? null,
            $navigationItem['data'] ?? []
        );
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getLink(): ?Typolink
    {
        return $this->link;
    }

    public function setLink(Typolink $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function getTarget(): string
    {
        return $this->link->getTarget();
    }

    public function setTarget(string $target): self
    {
        $this->link->setTarget($target);
        return $this;
    }

    public function getCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;
        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getSpacer(): bool
    {
        return $this->spacer;
    }

    public function setSpacer(bool $spacer): self
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

    public function setChildren(Navigation $children): self
    {
        $this->children = $children;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
}
