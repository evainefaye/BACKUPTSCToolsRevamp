<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	// Check Permissions records for user
	$userInfo = $_SESSION['userInfo'];

	if (!isset($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION']) || $getGUID == "") {
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
		echo "		permissions required: AGENTDOCUMENTATION: [ADD], [EDIT], [VIEW], [ALL]<br />\n";
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

	$agentStatus = "";
	if (isset($_POST['agentStatus'])) {
		if ($_POST['agentStatus'] == "ACTIVE ONLY") {
			$agentStatus = " AND lookupAgentInfo.agentStatus = 'ACTIVE'";
		}
	}

	$includeInactive = "AND lookupAgentInfo.agentStatus = 'ACTIVE'";
	if (isset($_POST['includeInactive'])) {
		if ($_POST['includeInactive'] == 'true') {
			$includeInactive = "";
		}
	}
	
	// Get passed supervisorGUID value if it exists
	if (isset($_POST['supervisorGUID'])) {
		$supervisorGUID = $_POST['supervisorGUID'];
	} else {
		$supervisorGUID = "";
	}
	// If supervsiorGUID was passsed, then get records based on the supervsiorId
	if ($supervisorGUID != "") {
		// Get list of agents from lookupAgentInfo and load information that correlates in lookupPermissions, generate JSON with GUID, LastName, First Name, attId, and DisplayName
		$sql = "SELECT * FROM lookupAgentInfo LEFT JOIN lookupPermissions ON lookupAgentInfo.GUID=lookupPermissions.GUID WHERE lookupPermissions.GUID IS NOT NULL AND lookupAgentInfo.supervisorGUID = '$supervisorGUID' $includeInactive ORDER BY lastName, firstName ASC";
	} else {
		// Get list of agents from lookupAgentInfo and load information that correlates in lookupPermissions, generate JSON with GUID, LastName, First Name, attId, and DisplayName
		$sql = "SELECT * FROM lookupAgentInfo LEFT JOIN lookupPermissions ON lookupAgentInfo.GUID=lookupPermissions.GUID WHERE lookupPermissions.GUID IS NOT NULL $includeInactive ORDER BY lastName, firstName ASC";
	}
	$results = $Database->Query($sql);
	while ($myrow = odbc_fetch_array($results)) {
		$guid = $myrow['GUID'];
		$firstName = $myrow['firstName'];
		$lastName = $myrow['lastName'];
		$attId = $myrow['attId'];
		$supervisorGUID = $myrow['supervisorGUID'];
		$agentStatus = $myrow['agentStatus'];
		$fullName = $lastName . ", " . $firstName;
		$displayName = $fullName . " [" . $attId . "]";
		$selAgentId[] = array(
			'GUID' => $guid,
			'fullName' => $fullName,
			'attId' => $attId,
			'displayName' => $displayName
		);

	}
	echo json_encode($selAgentId);
?>