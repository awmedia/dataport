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
	
	public function __construct(PDO $connection, $sqlQuery, array $params = null, $fetchMode = PDO::FETCH_ASSOC)
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
			$columnCount = $this->stmt->columnCount();
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
	
	private function getIterator()
	{
		if (!$this->iterator)
		{
			$this->stmt = $this->connection->prepare($this->sqlQuery);
			foreach ($this->params as $key => $value)
			{
				$this->stmt->bindValue($key, $value);
			}
			$this->stmt->execute();
			$this->iterator = new PdoStatementIterator($this->stmt, $this->fetchMode);
		}
		
		return $this->iterator;
	}
}