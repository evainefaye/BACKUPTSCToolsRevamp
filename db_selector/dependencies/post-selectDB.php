<?php
	session_start();
	if (isset($_POST['databaseClass'])) {
		$databaseClass = $_POST['databaseClass'];
	} else {
		exit;
	}
	$databaseClass = $_POST['databaseClass'];
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/" . $databaseClass);

	// Instantiate the database;
	$Database = new MyDatabase;
	// Connect to the Database
	// If a different Database Name, Host, or User/Password is Required update the information in database.class.php
	$Database->Connect();
	if (strpos($_SERVER['REMOTE_USER'],'/') !== false) {
		$cred = explode('/',$_SERVER['REMOTE_USER']);
	}
	if (strpos($_SERVER['REMOTE_USER'],'\\') !== false) {
		$cred = explode('\\',$_SERVER['REMOTE_USER']);
	}
	// split the domain and the user ID.  if further security is required, we can add the domain wayad as a check as well.
	if (strpos($_SERVER['REMOTE_USER'],'\\') !== false || strpos($_SERVER['REMOTE_USER'],'/') !== false) {
		if (count($cred) == 1) array_unshift($cred, "");
		list($domain, $loginId) = $cred;
	} else {
		$loginId = $_SERVER['REMOTE_USER'];
	}

	// check for permissions against login name
	$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE loginId='$loginId'";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$rowCount = $myrow['COUNT'];

	$databaseName = strtoupper(strtok($databaseClass,'.'));
	// No Entry in Permissions Table for Login Name Create Error and Exit
	if ($rowCount == 0) {
		$postResponse = array("responseType" => "error", "responseMessage" => "No Access To Database ($databaseName)", "autoCloseDelay" => "9000");
	} else {
		if (session_id() != '' || isset($_SESSION)) {
			$_SESSION['DATABASE']  = $databaseClass;
		}
		setcookie('DATABASE',$databaseClass,time() + (86400 * 365),'/'); // 86400 = 1 day
		$_SESSION['DATABASE'] = $databaseClass;
		$postResponse = array("responseType" => "success", "responseMessage" => "Processing Database Change ($databaseName)", "autoCloseDelay" => "2500");
	}
	echo json_encode($postResponse);

?>