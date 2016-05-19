<?php

	function mConnect(){
		$mysqli = new mysqli("localhost", "user", "pass", "db");
	        if ($mysqli->connect_errno) {
			$error="Failed to connect to MySQL: (".$msc->connect_errno.") ".$msc->connect_error;
			return($error);
	        }
		else{
			return($mysqli);
		}
	}

?>
