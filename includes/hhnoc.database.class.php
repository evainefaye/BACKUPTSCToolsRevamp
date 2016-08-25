<?php
 
	$databaseName = 'HH NOC';
	
	class MyDatabase {

// The var that stores the last used SQL statement
	var $SQLStatement = ""; 

// The var that stores the error (if any)
	var $Error = "";

		// Config for the database connection
		function MyDatabase() {
			$this->DBUser = "AWSTSC_Tools";
			$this->DBPass = "&CmeVY6nQ432";
			$this->DBName = "noc_hh_tools";
			$this->DBHost = "SQL-SNA-0003";
		}

		// Connect to the database
		function Connect() {
			$this->db = odbc_connect("Driver={SQL Server};Server=$this->DBHost;Database=$this->DBName;", $this->DBUser, $this->DBPass) or die("DATABASE CONNECTION ERROR: ". odbc_errormsg());
			if (!is_resource($this->db)) {
				$this->db = odbc_pconnect("Driver={SQL Server};Server=$this->DBHost;Database=$this->DBName;", $this->DBUser, $this->DBPass) or die("DATABASE CONNECTION ERROR: ". odbc_errormsg());
			}
			if (is_resource($this->db)) 
				return true;
			else  
				return false;
		}

		// Disconnect from the database
		function Disconnect() {
			$this->db = odbc_connect("Driver={SQL Server};Server=$this->DBHost;Database=$this->DBName;", $this->DBUser, $this->DBPass)  or die("DATABASE ERROR:". odbc_errormsg());
			odbc_close($this->db);
		}

		// Query the database
		function Query($sql) {
			$this->db = odbc_connect("Driver={SQL Server};Server=$this->DBHost;Database=$this->DBName;", $this->DBUser, $this->DBPass) or die("DATABASE CONNECTION ERROR: ". odbc_errormsg());
			if (!empty($sql)) {
				$query = odbc_exec($this->db,$sql) or die("DATABASE QUERY ERROR 1: ($sql) ". odbc_errormsg());
				return $query;
				echo gettype($query);
			} else { 
				$this->close_conn();
				return false;
			}
		}

    // Method that dynamically adds values to a MSSQL database table using the $_POST vars

    function AddToDB($tbl) {

      // Set the arrays we'll need

      $sql_columns = array();
      $sql_columns_use = array();
      $sql_value_use = array();

      // Pull the column names from the table $tbl

      $pull_cols = mssql_query("select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME =  " . $tbl) or die("DATABASE QUERY ERROR 2: " . odbc_errormsg());

      // Pull an associative array of the column names and put them into a non-associative array

      while ($columns = mssql_fetch_assoc($pull_cols)) {
        $sql_columns[] = $columns["COLUMN_NAME"];
      }

      foreach ($_POST as $key => $value) {

        // Check to see if the variables match up with the column names

        if (in_array($key, $sql_columns) && trim($value)) {

          // If this variable contains the string "DATESTAMP" then use MSSQL function NOW() 

          if ($value == "DATESTAMP") {
            $sql_value_use[] = "NOW()";
          } else {

            // If this variable contains a number, then don't add single quotes, otherwise check to see if magic quotes are on and use 
            // addslashes if they aren't

            if (is_numeric($value)) {
              $sql_value_use[] = $value;
            } else {
              $sql_value_use[] = (get_magic_quotes_gpc()) ? "'" . MSSQL_real_escape_string($value) . "'" : "'" . addslashes($value) . "'";
            }
          }

          // Put the column name into the array

          $sql_columns_use[] = $key;
        }
      }

      // If $sql_columns_use or $sql_value_use are empty then that means no values matched

      if ((sizeof($sql_columns_use) == 0) || (sizeof($sql_value_use) == 0)) {

        // Set $Error if no values matched

        $this->Error = "Error: No values were passed that matched any columns.";
        return false;
      } else {

        // Implode $sql_columns_use and $sql_value_use into an SQL insert sqlstatement

        $this->SQLStatement = "INSERT INTO " . $tbl . " (" . implode(",",$sql_columns_use) . ") VALUES (" . implode(",",$sql_value_use) . ")";

        // Execute the newly created statement

        if (@odbc_exec($this->db, $this->SQLStatement)) {
          return true;
        } else {

          // Set $Error if the execution of the statement fails

          $this->Error = "Error: " . odbc_errormsg();
          return false;
        }
      }
    }


      // Method that dynamically updates values in a MSSQL database table using the $_POST vars

  function UpdateDB($tbl, $id, $id_name) {
  
    // Set the arrays we'll need

    $sql_columns = array();
    $sql_value_use = array();
 
    // Pull the column names from the table $tbl

    $pull_cols = MSSQL_query("SHOW COLUMNS FROM " . $tbl) or die(  "MSSQL ERROR: " . MSSQL_error());

    // Pull an associative array of the column names and put them into a non-associative array

    while ($columns=MSSQL_fetch_assoc($pull_cols)) {
      $sql_columns[] = $columns["Field"];
    }

      foreach ($_POST as $key => $value) {

        // Check to see if the variables match up with the column names

        if (in_array($key, $sql_columns) && trim($value)) {

          // If this variable contains the string "DATESTAMP" then use MSSQL function NOW() 

          if ($value == "DATESTAMP") {
            $sql_value_use[] = "NOW()";
          } else {

            // If this variable contains a number, then don't add single quotes, otherwise check to see if magic quotes are on and use 
            // addslashes if they aren't

            if (is_numeric($value)) {
              $sql_value_use[$key] = $value;
            } else {
              $sql_value_use[$key] = (get_magic_quotes_gpc()) ? "'" . MSSQL_real_escape_string($value) . "'" : "'" . addslashes($value) . "'";
            }
          }

          // Put the column name into the array

          $sql_columns_use[] = $key;
        }
      }

      // If $sql_value_use is empty then that means no values matched

      if (sizeof($sql_value_use) == 0) {

        // Set $Error if no values matched
        $this->Error = "Error: No values were passed that matched any columns.";
        return false;
      } else {

        // Create the SQL Update Statement.

        $this->SQLStatement = "UPDATE " . $tbl . " SET ";
        foreach($sql_columns_use as $value) {
          $this->SQLStatement .= $value."=".$sql_value_use[$value].",";
        }
        $this->SQLStatement = substr($this->SQLStatement,0,-1);
        $this->SQLStatement .= " WHERE " . $id_name . "= '$id'";

        // Execute the newly created statement
        if (@odbc_exec($this->db, $this->SQLStatement)) {
          return true;
        } else {

          // Set $Error if the execution of the statement fails

          $this->Error = "Error: " . odbc_errormsg();
          return false;
        }
      }
    }

}
?>
