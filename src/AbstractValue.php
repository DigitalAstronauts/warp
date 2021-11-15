<?php
declare(strict_types=1);

namespace Warp;

abstract class AbstractValue implements \JsonSerializable
{
    abstract public function getValue();

    public function jsonSerialize(): string
    {
        $value = $this->getValue();
        if(is_scalar($value)) return $value;
        return json_encode($value);
    }
}