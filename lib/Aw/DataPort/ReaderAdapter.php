<?php

namespace Aw\DataPort;

/**
 * ReaderAdapter interface
 * @author Jerry Sietsma
 */
interface ReaderAdapter
{
    /**
     * Get rows
     * @return    object    Iterator
     */
    public function getRows();
    
    /**
     * Get row count
     * @return    int    Row count
     */
    public function getRowCount();
    
    /**
     * Get columns
     * @return    array    Array with columns
     */
    public function getColumns();
}