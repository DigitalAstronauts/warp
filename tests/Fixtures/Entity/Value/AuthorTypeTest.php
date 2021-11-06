<?php
declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity\Value;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class AuthorTypeTest extends TestCase
{
    public function test__construct(): void
    {
        $type = new AuthorType(AuthorType::TYPE_INTERNAL);
        Assert::assertInstanceOf(
            AuthorType::class,
            $type
        );
    }

    public function test__construct_ThrowsExceptionOnInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AuthorType('__not_defined__');
    }

}
