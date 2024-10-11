<?php declare(strict_types=1);

namespace SMS\FluidComponents\Domain\Model;

use Countable;
use Iterator;
use ReturnTypeWillChange;
use SMS\FluidComponents\Domain\Model\LanguageNavigationItem;
use SMS\FluidComponents\Domain\Model\NavigationItem;
use SMS\FluidComponents\Interfaces\ConstructibleFromArray;

/**
 * Data Structure to generate a navigation in components.
 */
class Navigation implements Iterator, Countable, ConstructibleFromArray
{
    /**
     * Navigation items.
     *
     * @var NavigationItem[]
     */
    protected array $items = [];

    /**
     * Initializes a navigation object from a TYPO3 navigation array.
     */
    public function __construct(array $items)
    {
        $this->setItems($items);
    }

    /**
     * Initializes a navigation object from a TYPO3 navigation array.
     */
    public static function fromArray(array $items): self
    {
        return new static($items);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        // Make sure that navigation items use the appropriate data structure
        $this->items = array_filter(array_map([$this, 'sanitizeNavigationItem'], $items));
        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * Makes sure that the provided item is a valid data structure.
     */
    protected function sanitizeNavigationItem(mixed $item): ?NavigationItem
    {
        if ($item instanceof NavigationItem) {
            return $item;
        }

        if (is_array($item)) {
            if (isset($item['languageId'])) {
                return LanguageNavigationItem::fromArray($item);
            } else {
                return NavigationItem::fromArray($item);
            }
        }

        return null;
    }
}
