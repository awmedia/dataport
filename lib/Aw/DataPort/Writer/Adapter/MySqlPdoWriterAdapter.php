<?php

namespace Aw\DataPort\Writer\Adapter;

use Aw\DataPort\Exception,
    \PDO;

/**
 * MySqlPdoWriterAdapter
 * @author Jerry Sietsma
 */
class MySqlPdoWriterAdapter extends AbstractDatabaseWriterAdapter
{
    const INSERT_MODE_INSERT = 'insert';

    const INSERT_MODE_REPLACE = 'replace';

    const INSERT_MODE_ON_DUPLICATE_KEY_UPDATE = 'onduplicatekeyupdate';

    protected $connection;

    protected $primaryKeyColumns;

    protected $insertMode;

    protected $columns;

    protected $createdDateColumn;

    protected $customOnDuplicateKeyUpdateColumns;
    
    protected $customOnDuplicateKeyUpdateColumnsToIgnore;

    /**
     * @var string     DateTime only updates when values are different
     */
    protected $changedDateColumn;

    /**
     * @var string     DateTime will allways be updated when INSERT_MODE_ON_DUPLICATE_KEY_UPDATE mode is enabled and unique key is matched
     */
    protected $touchedDateColumn;
    
    /**
     * @param     string 
     * @param     object 
     * @param     string     self::INSERT_MODE_*
     */
    public function __construct($tableName, PDO $connection, $insertMode = self::INSERT_MODE_INSERT, $autoResolvePrimaryKeyColumns = true)
    {
        parent::__construct($tableName);

        $this->connection = $connection;
        
        $this->sqlBuffer = null;
        $this->primaryKeyColumns = array();
        $this->insertMode = $insertMode;
        $this->onDuplicateKeyUpdateSqlSuffix = null;

        // find primary key when insert mode is "onduplicatekeyupdate"
        if ($this->insertMode === self::INSERT_MODE_ON_DUPLICATE_KEY_UPDATE && $autoResolvePrimaryKeyColumns === true)
        {
            $sql = "
                SELECT kcu.column_name AS `name`
                FROM   information_schema.key_column_usage AS kcu
                WHERE  table_schema = schema()             
                AND    constraint_name = 'PRIMARY'         
                AND    table_name = :tableName   
            ";
            
            if ($databaseName = $this->getDatabaseName())
            {
              $sql .= "
                  AND table_schema = :databaseName
              ";    
            }
            
            $stmt = $this->connection->prepare($sql);
            
            $stmt->bindValue('tableName', $tableName);
            
            if (isset($databaseName))
            {
                $stmt->bindValue('databaseName', $databaseName);
            }
            
            $stmt->execute();

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column)
            {
                $this->primaryKeyColumns[] = $column['name'];
            }
        }
    }
    
    /**
     * Public methods
     */

    public function enableCreatedDateOnColumn($columnName)
    {
        $this->createdDateColumn = $columnName;
        return $this;
    }

    public function enableChangedDateOnColumn($columnName)
    {
        $this->changedDateColumn = $columnName;
        return $this;
    }

    public function enableTouchedDateOnColumn($columnName)
    {
        $this->touchedDateColumn = $columnName;
        return $this;
    }

    public function setCustomOnDuplicateKeyUpdateColumns(array $columns)
    {
        $this->customOnDuplicateKeyUpdateColumns = $columns;

        if ($this->changedDateColumn !== null && !in_array($this->changedDateColumn, $columns))
        {
            $this->customOnDuplicateKeyUpdateColumns[] = $this->changedDateColumn;
        }

        if ($this->touchedDateColumn !== null && !in_array($this->touchedDateColumn, $columns))
        {
            $this->customOnDuplicateKeyUpdateColumns[] = $this->touchedDateColumn;
        }

        $this->onDuplicateKeyUpdateSqlSuffix = null;
    }
    
    public function setCustomOnDuplicateKeyUpdateColumnsToIgnore(array $columns)
    {
        $this->customOnDuplicateKeyUpdateColumnsToIgnore = $columns;
    }

    /**
     * Private/protected methods
     */

    protected function _writeRow(array $row)
    {
        if (!$this->columns)
        {
            $this->columns = array_keys($row);

            if ($this->changedDateColumn && !in_array($this->changedDateColumn, $this->columns))
            {
                $this->columns[] = $this->changedDateColumn;
            }

            if ($this->createdDateColumn && !in_array($this->createdDateColumn, $this->columns))
            {
                $this->columns[] = $this->createdDateColumn;
            }

            if ($this->touchedDateColumn && !in_array($this->touchedDateColumn, $this->columns))
            {
                $this->columns[] = $this->touchedDateColumn;
            }
        }

        if (!$this->sqlBuffer)
        {
            $isReplaceInsertMode = $this->insertMode === self::INSERT_MODE_REPLACE;
            $this->sqlBuffer = $isReplaceInsertMode ? 'REPLACE' : 'INSERT';
            $this->sqlBuffer .= " INTO " . ($this->getDatabase(true, '.')) . " " . $this->getTable(true) . " (`" . implode('`, `', $this->columns) . "`) VALUES ";
        }
        
        foreach ($row as $key => $value)
        {
            $row[$key] = $this->quoteValue($value);
        }

        if ($this->createdDateColumn)
        {
            $row[$this->createdDateColumn] = 'NOW()';
        }

        if ($this->changedDateColumn)
        {
            $row[$this->changedDateColumn] = 'NOW()';
        }

        if ($this->touchedDateColumn)
        {
            $row[$this->touchedDateColumn] = 'NOW()';
        }
        
        $this->sqlBuffer .= " (" . implode(", ", $row) . "), ";

        return true;
        
        /*
        $this->queryBufferCounter++;
        
        if ($this->queryBufferCounter >= $this->queryBufferRowsSize)
        {
            $this->flush();
        }
        */
    }
    
    public function flush()
    {
        if ($this->sqlBuffer)
        {
            $this->sqlBuffer = substr($this->sqlBuffer, 0, strlen($this->sqlBuffer) - 2);

            if ($this->insertMode === self::INSERT_MODE_ON_DUPLICATE_KEY_UPDATE)
            {
                if (!$this->onDuplicateKeyUpdateSqlSuffix)
                {
                    $this->onDuplicateKeyUpdateSqlSuffix = '';

                    $ignoreColumns = $this->primaryKeyColumns;

                    if ($this->createdDateColumn)
                    {
                        $ignoreColumns[] = $this->createdDateColumn;
                    }

                    $columns = $this->getOnDuplicateKeyColumns();

                    if ($this->changedDateColumn)
                    {
                        $ignoreColumns[] = $this->changedDateColumn;

                        $onDuplicateKeyUpdateColumns = array();
                        foreach ($columns as $column)
                        {
                            if ($column !== $this->changedDateColumn && $column !== $this->createdDateColumn && $column !== $this->touchedDateColumn)
                            {
                                $onDuplicateKeyUpdateColumns[] = '`' . $column . '` <> VALUES(`' . $column . '`)'; 
                            }
                        }

                        if (count($onDuplicateKeyUpdateColumns) > 0)
                        {
                            $this->onDuplicateKeyUpdateSqlSuffix .= "`" . $this->changedDateColumn . "` = CASE WHEN (" . implode(' OR ', $onDuplicateKeyUpdateColumns) . ") THEN NOW() ELSE `" . $this->changedDateColumn . "` END";
                        }
                    }

                    foreach ($columns as $column)
                    {
                        if (!in_array($column, $ignoreColumns))
                        {
                            if ($this->onDuplicateKeyUpdateSqlSuffix !== '')
                            {
                                $this->onDuplicateKeyUpdateSqlSuffix .= ', ';
                            }

                            if($this->touchedDateColumn && $this->touchedDateColumn === $column)
                            {
                                $this->onDuplicateKeyUpdateSqlSuffix .= '`' . $column . '` = NOW()';
                            }
                            else
                            {
                                $this->onDuplicateKeyUpdateSqlSuffix .= '`' . $column . '` = VALUES(`' . $column . '`)';
                            }
                        }
                    }
                }

                $this->sqlBuffer .= " ON DUPLICATE KEY UPDATE ";

                if ($this->onDuplicateKeyUpdateSqlSuffix !== '')
                {
                     $this->sqlBuffer .= $this->onDuplicateKeyUpdateSqlSuffix;
                }
                else
                {
                    // When ON DUPLICATE KEY UPDATE conditions are empty,
                    // set value of the first column to the current value to do at least something.
                    // otherwise the query will be terminated with an error.

                    $firstColumn = $this->columns[0];
                    $this->sqlBuffer .= ' `' . $firstColumn . '` = `' . $firstColumn . '`';
                }
            }
            
            $this->executeQuery($this->sqlBuffer . ';');
            
            $this->sqlBuffer = null;
            //$this->queryBufferCounter = 0;
        }
    }

    protected function executeQuery($query)
    {     
        $this->connection->query($query);
        $this->lastItemId = $this->connection->lastInsertId();
    }
    
    /**
     * Helper method
     */
    protected function getOnDuplicateKeyColumns()
    {
        $columns = $this->customOnDuplicateKeyUpdateColumns !== null ? $this->customOnDuplicateKeyUpdateColumns : $this->columns;
        
        if ($this->customOnDuplicateKeyUpdateColumnsToIgnore && count($this->customOnDuplicateKeyUpdateColumnsToIgnore) > 0)
        {
            $flipped = array_flip($columns);
            
            foreach ($this->customOnDuplicateKeyUpdateColumnsToIgnore as $column)
            {
                if (array_key_exists($column, $flipped))
                {
                    unset($flipped[$column]);
                }
            }
            
            $columns = array_flip($flipped);
        }
        
        return $columns;
    }
    
    /**
     * Helper method
     */
    protected function quoteValue($value)
    {
        // if value is NULL and DB column is of type int, the value in the DB will be 0 instead of NULL
        return is_null($value) ? 'NULL' : $this->connection->quote($value, $this->detectPdoParamType($value));
    }
    
    /**
     * Helper method
     */
    protected function detectPdoParamType($value)
    {
        if (is_null($value)) return PDO::PARAM_BOOL;
        if (is_bool($value)) return PDO::PARAM_BOOL;
        if (is_int($value)) return PDO::PARAM_INT;
        if (is_null($value)) return PDO::PARAM_BOOL;
        return PDO::PARAM_STR;
    }
}