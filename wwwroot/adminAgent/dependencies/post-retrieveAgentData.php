<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	
	// Check Permissions records for user
	$userInfo = $_SESSION['userInfo'];

	if (!isset($userInfo['userPermissions']['tools']['ADMINAGENT']) || $getGUID == "") {
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
		echo "		permissions required: ADMINAGENT: [ADD], [EDIT], [VIEW], [ALL], [SUPER]<br />\n";
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
	if ($userInfo['userPermissions']['tools']['ADMINAGENT'] == "ADD") {
		exit;
	}
	
	// Retrieve Agent Data
	if (!isset($_POST['GUID'])) {
		$GUID = "";
	} else {
		$GUID = $_POST['GUID'];
	}

	if ($GUID != "") {
		$sql = "SELECT * FROM lookupPermissions LEFT JOIN lookupAgentInfo ON lookupPermissions.GUID=lookupAgentInfo.GUID WHERE lookupPermissions.GUID = '$GUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$agentInfo['GUID'] = $GUID;	
		$agentInfo['loginId'] = $myrow['loginId'];
		$agentInfo['attId'] = $myrow['attId'];
		$agentInfo['firstName'] = $myrow['firstName'];
		$agentInfo['lastName'] = $myrow['lastName'];
		$agentInfo['emailAddress'] = $myrow['emailAddress'];
		$agentInfo['supervisorGUID'] = $myrow['supervisorGUID'];
		$agentInfo['disciplinaryStep'] = $myrow['disciplinaryStep'];
		$agentInfo['agentNotes'] = $myrow['agentNotes'];
		$agentInfo['agentGender'] = $myrow['agentGender'];
		$agentInfo['requireViewRestricted'] = $myrow['requireViewRestricted'];
		$agentInfo['agentStatus'] = $myrow['agentStatus'];
		$agentInfo['agentType'] = $myrow['agentType'];
		$agentInfo['hireDate'] = $myrow['hireDate'];
		$agentInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$agentInfo['ADMINAGENT'] = $userInfo['userPermissions']['tools']['ADMINAGENT'];
	} else {
		$agentInfo['GUID'] = $GUID;
		$agentInfo['loginId'] = '';
		$agentInfo['attId'] = '';
		$agentInfo['firstName'] = '';
		$agentInfo['lastName'] = '';
		$agentInfo['emailAddress'] = '';
		$agentInfo['supervisorGUID'] = '';
		$agentInfo['disciplinaryStep'] = '';
		$agentInfo['agentNotes'] = '';
		$agentInfo['agentGender'] = '';
		$agentInfo['requireViewRestricted'] = '';
		$agentInfo['agentStatus'] = '';
		$agentInfo['agentType'] = '';
		$agentInfo['hireDate'] = '';
		$agentInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$agentInfo['ADMINAGENT'] = $userInfo['userPermissions']['tools']['ADMINAGENT'];
	}
	// Include Indication if user May set/Change requireViewRestricted
	if (!isset($userInfo['userPermissions']['misc']['setRequireViewRestricted'])) {
		$agentInfo['enableRestricted'] = false;
	} else {
		$agentInfo['enableRestricted'] = true;
	}
	echo json_encode($agentInfo);
?>