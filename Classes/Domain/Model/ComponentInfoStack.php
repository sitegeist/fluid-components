<?php

namespace SMS\FluidComponents\Domain\Model;

/**
 * Data Structure to encapsulate component information stack
 */
class ComponentInfoStack
{
    /**
     * @var ComponentInfo[]
     */
    private $infoStack = [];

    public function push(ComponentInfo $componentInfo): void
    {
        $this->infoStack[] = $componentInfo;
    }

    public function pop(): ComponentInfo
    {
        if (count($this->infoStack) < 1) {
            throw new \LogicException('Can not pop off component info from empty stack', 1670450792);
        }
        return array_pop($this->infoStack);
    }

    public function current(): ComponentInfo
    {
        $componentInfo = $this->pop();
        $this->push($componentInfo);
        return $componentInfo;
    }
}
