<?php

namespace Aw\DataPort\Reader\Adapter;

use Aw\DataPort\ReaderAdapter,
    Aw\Common\Spl\Iterators\PdoStatementIterator,
    \PDO;

/**
 * PDO ReaderAdapter
 * @author Jerry Sietsma
 */
class PdoReaderAdapter implements ReaderAdapter
{
	/**
	 * @var	object	$connection	PDO connection instance
	 */
	protected $connection;
	
	protected $sqlQuery;
	
	protected $params;
	
	protected $fetchMode;
	
	public function __construct($connection, $sqlQuery, array $params = null, $fetchMode = PDO::FETCH_ASSOC)
	{
		$this->connection = $connection;
		$this->sqlQuery = $sqlQuery;
		$this->params = $params ?: array();
		$this->fetchMode = $fetchMode;
		
		$this->iterator = null;
		$this->columns = null;
		$this->stmt = null;
	}
		
	public function getRows()
	{
		return $this->getIterator();
	}
	
	public function getRowCount()
	{
		return $this->getIterator()->count();
	}
	
	public function getColumns()
	{
		if (!$this->columns)
		{
			$stmt = $this->getStmt();
			$columnCount = $stmt->columnCount();
			$columns = array();
			for ($i = 0; $i < $columnCount; $i++)
			{
				$columnMeta = $this->stmt->getColumnMeta($i);
				$columns[] = $columnMeta['name'];	
			}
			$this->columns = $columns;
		}
		
		return $this->columns;
	}
	
	/**
	 * Private/protected methods
	 */
	
	private function getStmt()
	{
		if (!$this->stmt)
		{
			$this->stmt = $this->connection->prepare($this->sqlQuery);
		}
		
		return $this->stmt;
	}
	
	protected function getIterator()
	{
		if (!$this->iterator)
		{
			$stmt = $this->getStmt();
			foreach ($this->params as $key => $value)
			{
				$stmt->bindValue($key, $value);
			}
			$stmt->execute();
			$this->iterator = new PdoStatementIterator($stmt, $this->fetchMode);
		}
		
		return $this->iterator;
	}
}