<?php
declare(strict_types=1);

namespace Warp\Tests\Fixtures\Entity\Proxy;

use Warp\EntityProxyTrait;
use Warp\Tests\Fixtures\Entity\Author;
use Warp\Tests\Fixtures\Entity\Book;

class BookProxy extends Book
{
    use EntityProxyTrait;

    public function getAuthor(): Author
    {
        $this->initializeProperty('author');
        return parent::getAuthor();
    }
}