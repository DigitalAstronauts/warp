<?php
declare(strict_types=1);

namespace Warp;

trait EntityProxyTrait
{
    public array $__lazyProperties__ = [];
    private function initializeProperty(string $propertyName): void
    {
        if (isset($this->__lazyProperties__[$propertyName])) {
            $this->__lazyProperties__[$propertyName]();
        }
    }
}