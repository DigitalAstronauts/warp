<?php
declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity\Value;


use Warp\AbstractValue;
use Webmozart\Assert\Assert;

class AuthorType extends AbstractValue
{
    const TYPE_INTERNAL = 'internal';
    const TYPE_EXTERNAL = 'external';

    public function __construct(
        private $value
    )
    {
        Assert::inArray(
            $this->value,
            self::getPossibleValues()
        );
    }

    public static function getPossibleValues(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    public function getValue()
    {
        return $this->value;
    }
}