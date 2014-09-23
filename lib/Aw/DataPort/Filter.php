<?php

namespace Aw\DataPort;

use Aw\Common\Utils\ArrayUtils as Arr;

/**
 * Filter
 * @author Jerry Sietsma
 */
class Filter
{
    protected $columns;
    
    protected $callable;
    
    protected $params;
    
    protected $hasCustomParamSignature = false;
    
    protected $valuePlaceholderIndexes;
    
    /**
     * Factory method
     * Helper to create single-line Filter instances
     * @info    The params will be forwarded to the constructor
     */
    public static function factory()
    {
        $class = new \ReflectionClass(get_called_class());
        return $class->newInstanceArgs(func_get_args());
    }
    
    /**
     * @param   mixed       String with column or array with column names
     * @param   callable    Filter callable. The filter callable should ta
     * @param   array       Params for filter callable or null. 
     */
    public function __construct($column, $callable, $params = null)
    {
        $this->columns = is_array($column) ? $column : array($column);
        $this->callable = $callable;
        $this->params = $params;
        
        if (is_array($params))
        {
            $result = array_filter($params, function($value) { return stristr($value, ':value'); } );
            
            if (count($result) > 0)
            {
                $this->valuePlaceHolderIndexes = array_keys($result);
                $this->hasCustomParamSignature = true;
            }
        }
    }
    
    /**
     * Accept?
     * When the filter returns false for one or more columns, the row will be ignored
     * @param   array   Row
     * @return  bool    True if accepted, false if not
     */
    public function accept($row)
    {
        $accept = 1;
        
        $params = $this->params ?: array();
        
        foreach ($this->columns as $column)
        {
            $value = Arr::get($row, $column);
            
            if (!$this->hasCustomParamSignature)
            {
                // prepend value as first param
                array_unshift($params, $value);
            }
            else
            {
                // replace placeholders with value
                $params = array();
                foreach ($this->valuePlaceHolderIndexes as $index)
                {
                    $params[$index] = strtr($params[$index], array(':value'=>$value));
                }
            }
            
            $accept &= call_user_func_array($this->callable, $params) === true;
        }
        
        return (bool) $accept;
    }
}