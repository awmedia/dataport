<?php

namespace Aw\DataPort;

/**
 * Reader
 * This class controls the ReaderAdapter.
 * It can be extended to support logging for example.
 * @author Jerry Sietsma
 */
class Reader
{
    protected $adapter;
    
    public function __construct(ReaderAdapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    /**
     * Get ReaderAdapter
     * @return  object  ReaderAdapter instance
     */
    public function getAdapter()
	{
    	return $this->adapter;
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