<?php

namespace Aw\DataPort\Writer\Adapter;

use Aw\DataPort\WriterAdapter,
    Aw\DataPort\Exception;

/**
 * Abstract Writer Adapter
 * This class is not required, but implements some usefull features
 * like buffering, flushing and write counter
 * @author Jerry Sietsma
 */
abstract class AbstractWriterAdapter implements WriterAdapter
{
    /**
     * @cfg int Buffer size: 1 = write instantly, > 1 = use buffer (1 = default)
     */
    private $bufferSize = 1;
    
    private $_writeCount = 0;
    
    public function setBufferSize($size)
    {
        $this->bufferSize = $size;
    }
    
    public function writeRow(array $row)
    {
        $success = $this->_writeRow($row);
        
        if ($success)
        {
            $this->_writeCount++;
        }
        
        if ($this->_writeCount >= $this->bufferSize)
        {
            $this->flush();
        }
    }
    
    /**
     * Sub class specific writeRow implementation
     * @return  bool    Success?
     */
    abstract protected function _writeRow(array $row);
    
    /**
     * Override for custom implementation
     * Not required to implement, but when used, it should be implemented
     */
    public function getResult()
    {
        throw new Exception('The get result method is not implemented in ' . get_called_class());
    }
    
    /**
     * Implement when required
     * @return  void
     */
    public function flush()
    {
        if ($this->bufferSize > 1)
        {
            throw new Exception('The buffer is enabled (> 1) but the flush method is not implemented in ' . get_called_class());
        }
    }
    
    /**
     * Private/protected methods
     */

     protected function getWriteCount()
     {
         return $this->_writeCount;
     }
}