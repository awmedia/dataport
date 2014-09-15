<?php

namespace Aw\DataPort;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * DataPorter
 * @author Jerry Sietsma
 */
class DataPorter
{
    protected $reader;
    
    protected $writer;
    
    protected $mapper;
    
    protected $processors;
    
    protected $autoFlushWriter = true;
    
    public function __construct()
    {
        $this->processors = new ArrayCollection();
    }
    
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
        return $this;
    }
    
    public function getReader()
    {
        return $this->reader;
    }
    
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
        return $this;
    }
    
    public function getWriter()
    {
        return $this->writer;
    }
    
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }
    
    public function getMapper()
    {
        return $this->mapper;
    }
    
    public function getProcessors()
    {
        return $this->processors;
    }
    
    public function setAutoFlushWriter($autoFlush)
    {
        $this->autoFlushWriter = (bool) $autoFlush;
        return $this;
    }
    
    public function port()
    {
        $counter = 0;
        $result = null;
        $success = false;
        $exception = null;
        
        try
        {
            $sourceColumns = $this->reader->getColumns();

            foreach ($this->reader->getRows() as $sourceRow)
            {
                $mappedRow = $this->mapper ? $this->mapper->mapSourceToDestinationRow($sourceRow) : $sourceRow;
                
                $processedRow = $mappedRow;
                foreach ($this->getProcessors() as $processor)
                {
                    $processedRow = $processor->process($mappedRow, $sourceRow, $sourceColumns, $this->mapper);
                }
                
                $this->writer->writeRow($processedRow);
                                
                $counter++;
            }
            
            if ($this->autoFlushWriter)
            {
                $this->writer->flush();
            }
            
            $result = $this->writer->getResult();
            $success = true;
        }
        catch(Exception $e)
        {
            $exception = $e;
        }
        
        return new DataPorterResult($success, $writerResult, $rowsPortedCounter, $exception);
    }
}