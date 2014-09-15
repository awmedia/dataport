<?php

namespace Aw\DataPort;

use \Exception;

/**
 * Reader
 * @author Jerry Sietsma
 */
class Reader
{
    protected $adapter;
    
    public function __construct(ReaderAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    public function getRows()
    {
        return $this->adapter->getRows();
    }
    
    public function getColumns()
    {
        return $this->adapter->getColumns();
    }
    
    public function getRowCount()
    {
        return $this->adapter->getRowCount();
    }
}