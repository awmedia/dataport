<?php

namespace Aw\DataPort\Writer\Adapter;

use Aw\DataPort\Exception,
    \PDO;

//use Rapid\utils\File;

/**
 * MySqlDumpWriterAdapter
 * Write rows to MySql dump file
 * @author Jerry Sietsma
 */
class MySqlQueryFileWriterAdapter extends AbstractWriterAdapter
{
    protected $filename;
    
    /**
     * @param     string     Table name
     * @param     object     PDO connection (required for quotes)
     * @param     string     Insert Mode     self::INSERT_MODE_*
     */
    public function __construct($tableName, PDO $connection, $insertMode = self::INSERT_MODE_INSERT, $filename)
    {
        $this->filename = $filename;
        
        parent::__construct($tableName, $connection, $insertMode, false);
        
        //$this->filename = File::uniqueFilename($filename);
        
        file_put_contents($this->filename, '', LOCK_EX);
    }

    /**
     * AbstractDbWriterAdapter methods
     */
     
    public function getLastInsertId()
    {
        throw new RapidException(get_called_class() . ' does not support getLastInsertId'); 
    }

    protected function executeQuery($query)
    {     
        file_put_contents($this->filename, $query . "\n", FILE_APPEND | LOCK_EX);
    }
}