<?php

namespace Aw\DataPort;

/**
 * Result
 * DataPorter result object. 
 * After construction this object is read-only
 * @author Jerry Sietsma
 */
class Result
{
    protected $success;
    
    protected $writerResult;
    
    protected $rowCount;
    
    protected $exception;
    
    /**
     * @param    bool       True if success, false if not
     * @param    mixed      Result returned by the Writer or null
     * @param    int        Row count
     * @param    object     Exception instance or null
     */
    public function __construct($success, $writerResult, $rowCount, $filteredCount, Exception $exception = null)
    {
        $this->success = (bool) $success;
        $this->writerResult = $writerResult;
        $this->rowCount = $rowCount;
        $this->filteredCount = $filteredCount;
        $this->exception = $exception;
    }
    
    public function getSuccess()
    {
        return $this->success;
    }
    
    public function getWriterResult()
    {
        return $this->writerResult;
    }
    
    public function getRowCount()
    {
        return $this->rowCount;
    }
    
    public function getFilteredCount()
    {
        return $this->filteredCount;
    }
    
    public function getException()
    {
        return $this->exception;
    }
}