<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	
	// Check Permissions records for user
	$userInfo = $_SESSION['userInfo'];

	if (!isset($userInfo['userPermissions']['tools']['ADMINSUPERVISOR']) || $getGUID == "") {
		$webpage = $_SERVER['PHP_SELF'];
		$hostName = $_SERVER['HTTP_HOST']; 			
		echo "<html>\n";
		echo "	<head>\n";
		echo "		<title>Error!</title>\n";
		echo "	</head>\n";
		echo "	<body>\n";
		echo "		<h3>Error!</h3>\n";
		echo "		instance: $databaseName<br />\n";				
		echo "		login_id:  $loginId<br />\n";
		echo "		permissions required: ADMINSUPERVISOR: [ADD], [EDIT], [ALL], [SUPER]<br />\n";
		echo "		module:  $webpage<br />\n";
		echo "		<pre>\n";
		if (isset($userInfo['userPermissions']['tools'])) {
			echo "		<pre>\n";
			var_dump($userInfo['userPermissions']['tools']);
			echo "		</pre>\n";
		} else {
			echo "No user permissions were loaded.<br />";
		}
		echo "		You may click <a href='http://$hostName/db_selector/'>HERE</a> to switch Databases if necessary.\n";
		echo "	</body>\n";
		echo "</html>\n";
		exit;
	}

	// Do not retrieve data if you only have ADD perimissions
	if ($userInfo['userPermissions']['tools']['ADMINSUPERVISOR'] == "ADD") {
		exit;
	}

	// Retrieve Supervisor Data
	if (!isset($_POST['GUID'])) {
		$GUID = "";
	} else {
		$GUID = $_POST['GUID'];
	}

	if ($GUID != "") {
		$sql = "SELECT * FROM lookupPermissions LEFT JOIN lookupSupervisorInfo ON lookupPermissions.GUID=lookupSupervisorInfo.GUID WHERE lookupPermissions.GUID = '$GUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$supervisorInfo['GUID'] = $GUID;	
		$supervisorInfo['loginId'] = $myrow['loginId'];
		$supervisorInfo['attId'] = $myrow['attId'];
		$supervisorInfo['firstName'] = $myrow['firstName'];
		$supervisorInfo['lastName'] = $myrow['lastName'];
		$supervisorInfo['emailAddress'] = $myrow['emailAddress'];
		$supervisorInfo['supervisorStatus'] = $myrow['supervisorStatus'];
		$supervisorInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$supervisorInfo['ADMINSUPERVISOR'] = $userInfo['userPermissions']['tools']['ADMINSUPERVISOR'];
	} else {
		$supervisorInfo['GUID'] = $GUID;
		$supervisorInfo['loginId'] = '';
		$supervisorInfo['attId'] = '';
		$supervisorInfo['firstName'] = '';
		$supervisorInfo['lastName'] = '';
		$supervisorInfo['emailAddress'] = '';
		$supervisorInfo['supervisorStatus'] = '';
		$supervisorInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$supervisorInfo['ADMINSUPERVISOR'] = $userInfo['userPermissions']['tools']['ADMINSUPERVISOR'];
	}
	echo json_encode($supervisorInfo);
?>