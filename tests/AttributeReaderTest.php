<?php
declare(strict_types=1);

namespace Warp\Tests;

use AttributeTest;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Warp\Tests\Fixtures\Entity\Author;

class AttributeReaderTest extends TestCase
{
    public function testRuntime()
    {
        $ro = new \ReflectionClass(Author::class);
        dump($ro->getAttributes());die;
    }
}
