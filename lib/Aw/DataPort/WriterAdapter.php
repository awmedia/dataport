<?php

namespace Aw\DataPort;

/**
 * WriterAdapter interface
 * @author Jerry Sietsma
 */
interface WriterAdapter
{
	/**
	 * Write row
	 * @param	array  Row to write
	 * @return	bool   True if success, false if not
	 */
	public function writeRow(array $row);
	
	/**
	 * Get result
	 * Some writers store data in memory like the ArrayWriterAdapter.
	 * Use this method to get that result
	 * @return	mixed
	 */
	public function getResult();
	
	/**
	 * Flush
	 * Sometimes data will be buffered before it will be written to the destination. 
	 * This method will flush the buffer and write data to destination.
	 * @return	bool  True if success, false if not
	 */
	public function flush();
}