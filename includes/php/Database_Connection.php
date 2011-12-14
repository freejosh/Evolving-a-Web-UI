<?php
class Database_Connection {
	/**
	 * @var resource The variable to store the database resource for the current connection.
	 */
	private $db;

	/**
	 * Opens the database connection.
	 *
	 * @param string $database The name of the database to select.
	 * @param string $username The username to use to connect to the database.
	 * @param string $password The password to use to connect to the database.
	 * @param string $address The address of the database. Defaults to localhost.
	 *
	 * @return bool True on success, throws Exception on error.
	 */
	function __construct($database, $username, $password, $address = 'localhost', $new_link = true) {
		if ($this->db = @mysql_connect($address, $username, $password, $new_link)) {
			if (@mysql_select_db($database, $this->db)) return true;
			throw new Exception("Could not select '$database'.");
		}
		throw new Exception("Could not connect to '$address' as '$username'. Error: ".mysql_error().' Code: '.mysql_errno());
	}

	/**
	 * Closes the database connection. Not always necessary but useful for minimizing script resources.
	 *
	 * @return bool True on success, throws Exception on error.
	 */
	function close() {
		if (!$this->db) throw new Exception("No database connection found.");

		if (@mysql_close($this->db)) return true;
		else throw new Exception("Could not close connection.");
	}

	/**
	 * Queries the database using the specified MySQL query.
	 *
	 * @param string $query The query.
	 * @global int _total_queries Sets and/or increments $_total_queries for debug purposes.
	 * @global array _querylog Sets and/or adds to $_querylog for debug purposes.
	 *
	 * @return array Array of result rows, throws Exception on error.
	 */
	function q($query) {
		if (!$this->db) throw new Exception("No database connection found.");

		$querytype = strtoupper(substr($query, 0, strpos($query, ' ')));
		$result = @mysql_query($query, $this->db);

		global $_total_queries, $_querylog;
		$_total_queries++;
		$backtrace = debug_backtrace();
		$file = substr($backtrace[0]['file'], strrpos($backtrace[0]['file'], '/')+1);
		$_querylog[] = "$file > {$backtrace[0]['line']} : $query";

		if (!$result) throw new Exception("Query could not be completed.");

		if ($querytype == 'SELECT' || $querytype == 'SHOW' || $querytype == 'DESCRIBE' || $querytype == 'EXPLAIN') {
			$output = array();
			if (mysql_num_rows($result) > 0) while($row = mysql_fetch_assoc($result)) $output[] = $row;
			mysql_free_result($result);
			return $output;
		}

		return true; // result is positive, but don't need to return anything
	}
}
?>