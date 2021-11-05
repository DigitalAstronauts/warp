<?php
declare(strict_types=1);

namespace Warp;

use Closure;
use Iterator;
use Nette\Database\Table\Selection;

class EntityIterator implements Iterator
{

    private Iterator $iterator;
    private Closure $closure;

    public function __construct(Iterator $iterator, Closure $closure)
    {
        $this->iterator = $iterator;
        $this->closure = $closure;
    }

    public function current(): Entity
    {
        return ($this->closure)($this->iterator->current());
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->iterator, $name)) {
            call_user_func_array([$this->iterator, $name], $arguments);
        }
    }


    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

}
