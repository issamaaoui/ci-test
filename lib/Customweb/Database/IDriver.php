<?php



interface Customweb_Database_IDriver {

	/**
	 * Starts a database transaction.
	 * 
	 * @return void
	 */
	public function beginTransaction();

	/**
	 * Commits the database transaction.
	 * 
	 * @return void
	 */
	public function commit();

	/**
	 * Abords the current database transaction.
	 * 
	 * @return void
	 */
	public function rollBack();
	
	/**
	 * Returns true, when a database transaction is 
	 * running. Hence the calling of beginTransaction()
	 * will throw an exception.
	 * 
	 * @return boolean
	 */
	public function isTransactionRunning();

	/**
	 *
	 * @param string $query        	
	 * @return Customweb_Database_IStatement
	 */
	public function query($query);

	public function quote($string);

	public function insert($tableName, $data);

	public function update($tableName, $values, $whereClause);

	public function remove($tableName, $whereClause);
}