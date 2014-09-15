<?php

namespace Aw\DataPort\Reader\Adapter;

use Aw\DataPort\ReaderAdapter, 
    \Iterator,
    \ArrayIterator;

/**
 * ArrayReaderAdapter
 * @author Jerry Sietsma
 */
class ArrayReaderAdapter implements ReaderAdapter
{
    protected $items;
    
    public function __construct(array $items)
    {
        $this->items = $items;
        $this->iterator = ($items instanceof Iterator) ? $items : null;
    }
        
    public function getRows()
    {
        if (!$this->iterator)
        {
            $this->iterator = new ArrayIterator($this->items);
        }
        
        return $this->iterator;
    }
    
    public function getRowCount()
    {
        return count($this->items);
    }
    
    public function getColumns()
    {
        return isset($this->items[0]) ? array_keys($this->items[0]) : null;
    }
}