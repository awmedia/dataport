<?php

namespace Aw\DataPort;

use Doctrine\Common\Collections\ArrayCollection,
    Iterator;

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
        $this->preProcessorFilters = new ArrayCollection();
        $this->postProcessorFilters = new ArrayCollection();
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
    
    /**
     * Get processores
     * Use this method to add processors to the collection.
     * Valid processors are: Callables or Processor instances (subclasses)
     * @return  object  ArrayCollection
     */
    public function getProcessors()
    {
        return $this->processors;
    }
    
    public function getPreProcessorFilters()
    {
        return $this->preProcessorFilters;
    }
    
    public function getPostProcessorFilters()
    {
        return $this->postProcessorFilters;
    }
    
    public function setAutoFlushWriter($autoFlush)
    {
        $this->autoFlushWriter = (bool) $autoFlush;
        return $this;
    }
    
    public function port()
    {
        $count = 0;
        $result = null;
        $success = false;
        $exception = null;
        $filteredCount = 0;
        
        try
        {
            $sourceColumns = $this->reader->getColumns();
            
            $rows = $this->reader->getRows();
            
            if (!($rows instanceof Iterator))
            {
                throw new Exception('The ReaderAdapter should return an Iterator');
            }

            foreach ($rows as $sourceRow)
            {
                $mappedRow = $this->mapper ? $this->mapper->mapSourceToDestinationRow($sourceRow) : $sourceRow;
                
                if (!$this->acceptRowByFilters($mappedRow, $this->preProcessorFilters))
                {
                    $filteredCount++;
                    continue;
                }
                
                $processedRow = $mappedRow;
                foreach ($this->getProcessors() as $processor)
                {
                    if ($processor instanceof Processor)
                    {
                        $processedRow = $processor->process($processedRow, $sourceRow, $sourceColumns, $this->mapper);
                    }
                    else
                    {
                        $processedRow = call_user_func($processor, $processedRow, $sourceRow, $sourceColumns, $this->mapper);
                    }
                }
                
                if (!$this->acceptRowByFilters($processedRow, $this->postProcessorFilters))
                {
                    $filteredCount++;
                    continue;
                }
                
                $this->writer->writeRow($processedRow);
                                
                $count++;
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
        
        return new Result($success, $result, $count, $filteredCount, $exception);
    }
    
    /**
     * Private/protected function
     */
    
    protected function acceptRowByFilters($row, $filters)
    {
        foreach ($filters as $filter)
        {
            if (!$filter->accept($row))
            {
                return false;
            }
        }
        
        return true;
    }
}