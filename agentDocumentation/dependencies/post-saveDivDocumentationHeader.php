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

	// Safeguard if there is no field and value, this is being called incorrectly close out and do nothing
	if (!isset($_POST['GUID']) || !isset($_POST['field']) || !isset($_POST['value'])) {
			$type = "error";
			$message = "NO DATA WAS SENT";
	}

	$GUID = $_POST['GUID'];
	$field = $_POST['field'];
	$value = $_POST['value'];
	
	if ($field == "supervisorGUID") {
		if (!isset($userInfo['userPermissions']['tools']['ADMINAGENT'])) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT AGENT RECORDS";
			goto echoResponse;
		}
		if (($userInfo['userPermissions']['tools']['ADMINAGENT'] != "EDIT" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") && $GUID == $userInfo['GUID']) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT THIS AGENT RECORD";
			goto echoResponse;
		}
		$type = "success";
		$message = "AGENT SUPERVISOR UPDATED";
		goto updateLookupAgentInfo;
	}

	if ($field == "disciplinaryStep") {
		if (!isset($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'])) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT AGENT RECORDS";
			goto echoResponse;
		}
		if (($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] != "ADD" && $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] != "EDIT" && $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] != "ALL") && $GUID == $userInfo['GUID']) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT THIS AGENT RECORD";
			goto echoResponse;
		}

		$type = "success";
		$message = "AGENT DISCIPLINARY STEP UPDATED";
		goto updateLookupAgentInfo;
	}

	if ($field == "agentNotes") {
		if (!isset($userInfo['userPermissions']['tools']['ADMINAGENT'])) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT AGENT RECORDS";
			goto echoResponse;
		}
		if (($userInfo['userPermissions']['tools']['ADMINAGENT'] != "EDIT" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") && $GUID == $userInfo['GUID']) {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT THIS AGENT RECORD";
			goto echoResponse;
		}
		$value = str_replace("'", "''", $value);
		$type = "success";
		$message = "AGENT NOTES UPDATED";
		goto updateLookupAgentInfo;
	}

	updateLookupAgentInfo:
	$sql = "UPDATE lookupAgentInfo SET $field = '$value' WHERE GUID = '$GUID'";
	$result = $Database->Query($sql);
	goto echoResponse;

	echoResponse:
	$response = array(
		'type' => $type,
		'message' => $message,
		'field' => ""
	);
	echo json_encode($response);
?>