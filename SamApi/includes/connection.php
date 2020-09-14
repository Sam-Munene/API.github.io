<?php

/**
 * 
 */
class DbConnect
{
	private $con;
	
	function connect()
	{
		include_once ('constants.php');

		$this->con = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		if (mysqli_connect_errno()) {
			echo "Failed to connect" .mysqli_connect_errno();
			return null;
		}
		return $this->con;
	}
}