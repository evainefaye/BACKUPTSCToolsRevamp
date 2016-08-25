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


	// If nothing was sent, then do nothing
	if (!isset($_POST)) {
		$type = "error";
		$message = "FAILED TO RECEIVE DATA FROM THE FORM. TRY AGAIN";
		$field = "";
		goto echoResponse;
	}


	// Ensure full required dataset was sent, if not, then we will do nothing
	if (($userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER" && (!isset($_POST['lastName']) || !isset($_POST['firstName']) || !isset($_POST['attId']) || !isset($_POST['loginId']) || !isset($_POST['emailAddress']) || !isset($_POST['supervisorGUID']) || !isset($_POST['agentType']) || !isset($_POST['hireDate']) || !isset($_POST['agentGender']) || !isset($_POST['requireViewRestricted']) || !isset($_POST['agentNotes']) || !isset($_POST['agentStatus']))) && ($userInfo['userPermissions']['tools']['ADMINAGENT'] == "SUPER" && (!isset($_POST['lastName']) || !isset($_POST['firstName']) || !isset($_POST['emailAddress']) || !isset($_POST['supervisorGUID']) || !isset($_POST['agentType']) || !isset($_POST['hireDate']) || !isset($_POST['agentGender']) || !isset($_POST['requireViewRestricted']) || !isset($_POST['agentNotes']) || !isset($_POST['agentStatus'])))) {
		$type = "error";
		$message = "FAILED TO RECEIVE ALL NECESSARY DATA FROM THE FORM. TRY AGAIN";
		$field = "";
		goto echoResponse;
	}
	
	$GUID = $_POST['GUID'];
	$lastName = $_POST['lastName'];
	$firstName = $_POST['firstName'];
	$attId = $_POST['attId'];
	$loginId = $_POST['loginId'];
	$emailAddress = $_POST['emailAddress'];
	$supervisorGUID = $_POST['supervisorGUID'];
	$agentType = $_POST['agentType'];
	$hireDate = $_POST['hireDate'];
	$agentGender = $_POST['agentGender'];
	$requireViewRestricted = $_POST['requireViewRestricted'];
	$agentNotes = $_POST['agentNotes'];
	$agentStatus = $_POST['agentStatus'];

	
	// Replace any single ' marks with '' or any " with "" to deal with SQL 
	$lastName = str_replace("'", "''", $lastName);
	$firstName = str_replace("'", "''", $firstName);
	$attId = str_replace("'", "''", $attId);
	$loginId = str_replace("'", "''", $loginId);
	$emailAddress = str_replace("'", "''", $emailAddress);
	$notesNotes = str_replace("'", "''", $agentNotes);

	if ($GUID == $_SESSION['userInfo']['GUID']) {
		// You are editing your own record, don't do anything
		$type = "error";
		$field = "";
		$message = "FOR SECURITY REASONS YOU MAY NOT EDIT YOU MAY NOT EDIT YOUR OWN RECORD";
		$field = "";
		goto echoResponse;
	}

	if ($attId == $_SESSION['userInfo']['attId']) {
		// Your trying to set a record to your own ATT ID don't do anything
		$type = "error";
		if ($_POST['GUID'] == "") {
			$message = "FOR SECURITY REASONS YOU MAY NOT CREATE A RECORD WITH YOUR OWN AT&T ID";
			$field = ".attId";
		} else {
			$message = "FOR SECURITY REASONS YOU MAY NOT EDIT A RECORD WITH YOUR OWN AT&T LOGIN";
			$field = ".loginId";
		}
		goto echoResponse;
	}

	if ($loginId == $_SESSION['userInfo']['loginId']) {
		// You are trying to set a record to your login Id, don't do anything
		$type = "error";
		if ($_POST['GUID'] == "") {
			$message = "FOR SECURITY REASONS YOU MAY NOT CREATE A RECORD USING YOUR OWN WINDOWS LOGIN";
			$field = ".loginId";
		} else {
			$message = "FOR SECURITY REASONS YOU MAY NOT EDIT A RECORD TO HAVE YOUR OWN WINDOWS LOGIN";
			$field = ".loginId";
		}
		goto echoResponse;
	}

	// Check if attId exists in lookupPermissions for another GUID
	if ($GUID != "") {
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE GUID != '$GUID' AND attId = '$attId'";
	} else {
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE attId = '$attId'";		
	}
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$count = $myrow['COUNT'];
	if ($count > 0) {
		$type = "error";
		$message = "AT&T ID OF " . strtoupper($attId) . " IS ASSIGNED TO ANOTHER USER. VALUE MUST BE UNIQUE";
		$field = ".attId";
		goto echoResponse;
	}


	// Check if loginId exists in lookupPermissions
	if ($GUID != "") {
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE GUID != '$GUID' AND loginId = '$loginId'";
	} else {
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE loginId = '$loginId'";		
	}
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$count = $myrow['COUNT'];
	if ($count > 0) {
		// loginId already exists, should be unique
		$type = "error";
		$message = "WINDOWS LOGIN OF " . strtoupper($loginId) . " IS ASSIGNED TO ANOTHER USER. VALUE MUST BE UNIQUE";
		$field = ".loginId";
		goto echoResponse;
	}


	// Set flag to indicate if permissions need to be checked/updated to false
	$updatePermissions = false;

	// Check if GUID exists in lookupPermissions
	if ($GUID != "") {
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE GUID = '$GUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$count = $myrow['COUNT'];
	} else {
		$count = 0;
	}
	if ($count < 1) {
		// There was no record found for the provided GUID in lookupPermissions, insert the record
		// Generate a GUID
		if ($GUID == "") {
			$sql = "SELECT NEWID() AS GUID";
			$results = $Database->Query($sql);
			$myrow = odbc_fetch_array($results);
			$GUID = $myrow['GUID'];
		}
		// Add Record to lookupPermissions
		if ($userInfo['userPermissions']['tools']['ADMINAGENT'] != "ADD" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO ADD AGENTS";
			$field = "";
			goto echoResponse;
		}
		$sql = "INSERT INTO lookupPermissions (GUID, lastName, firstName, attId, loginId, emailAddress) VALUES ('$GUID', '$lastName', '$firstName', '$attId', '$loginId', '$emailAddress')";
		$result = $Database->Query($sql);
		// Set flag to indicate we need to update permissions)
		$updatePermissions = true;
		// Get the current year and month
		$reportingPeriod = date('Y-m');
		// Add agent to lookupTeamHistory
		$sql = "INSERT INTO lookupTeamHistory (reportingPeriod, agentGUID, supervisorGUID) VALUES('$reportingPeriod', '$GUID', '$supervisorGUID')";
		$results = $Database->Query($sql);				
	} else {
		if ($userInfo['userPermissions']['tools']['ADMINAGENT'] != "EDIT" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT AGENTS";
			$field = "";
			goto echoResponse;
		}
		// There was a record found for the provided GUID in lookupPermissions, update the record
		$sql = "UPDATE lookupPermissions SET";
		$sql .= " GUID ='$GUID',";
		$sql .= " lastName = '$lastName',";
		$sql .= " firstName = '$firstName',";
		if ($userInfo['userPermissions']['tools']['ADMINAGENT'] == "SUPER") {
			$sql .= " attId = '$attId',";
			$sql .= " loginId = '$loginId',";
		}
		$sql .= " emailAddress = '$emailAddress'";
		$sql .= " WHERE GUID = '$GUID'";
		$results = $Database->Query($sql);
	}

	// Check if GUID exists in lookupAgentInfo
	$sql = "SELECT COUNT(*) AS COUNT FROM lookupAgentInfo WHERE GUID = '$GUID'";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$count = $myrow['COUNT'];

	if ($count < 1) {
		if ($userInfo['userPermissions']['tools']['ADMINAGENT'] != "ADD" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO ADD AGENTS";
			$field = "";
			goto echoResponse;
		}
		// There was no record found for the provided GUID in lookupAgentInfo, insert the record
		$sql = "INSERT  INTO lookupAgentInfo (GUID, supervisorGUID, agentType, hireDate, agentGender, requireViewRestricted, agentStatus, agentNotes, disciplinaryStep) VALUES ('$GUID', '$supervisorGUID', '$agentType', '$hireDate', '$agentGender', '$requireViewRestricted', '$agentStatus', '$agentNotes', 'NONE')";
		$results = $Database->Query($sql);
	} else {
		// There was a record found for the provided GUID in lookupAgentInfo, update the record
		// Before we begin the update, capture the current status of agentStatus so we can evaluate if its changing
		if ($userInfo['userPermissions']['tools']['ADMINAGENT'] != "EDIT" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "ALL" && $userInfo['userPermissions']['tools']['ADMINAGENT'] != "SUPER") {
			$type = "error";
			$message = "YOU DO NOT HAVE PERMISSIONS TO EDIT AGENTS";
			$field = "";
			goto echoResponse;
		}
		$sql = "SELECT agentStatus FROM lookupAgentInfo WHERE GUID='$GUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		if ($agentStatus != $myrow['agentStatus']) {
			// Agent Status was changed, flag this for being checked for perissios needing to be modified
			$updatePermissions = true;
		}
		$sql = "UPDATE lookupAgentInfo SET";
		$sql .= " supervisorGUID = '$supervisorGUID',";
		$sql .= " agentType = '$agentType',";
		$sql .= " hireDate = '$hireDate',";
		$sql .= " agentGender = '$agentGender',";
		$sql .= " requireViewRestricted = '$requireViewRestricted',";
		$sql .= " agentStatus = '$agentStatus',";
		$sql .= " agentNotes = '$agentNotes'";
		$sql .= " WHERE GUID = '$GUID'";
		$results = $Database->Query($sql);
		$reportingPeriod = date('Y-m');
		// Update lookupTeamHistory with current supervisor assignment for the current month
		$sql = "UPDATE lookupTeamHistory SET ";
		$sql .= " supervisorGUID = '$supervisorGUID'";
		$sql .= " WHERE reportingPeriod = '$reportingPeriod' AND agentGUID='$GUID'";
		$results = $Database->Query($sql);
	}

	if ($updatePermissions) {
		// Get Count of Active Agent Records matching GUID
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupAgentInfo WHERE GUID = '$GUID' AND agentStatus = 'ACTIVE'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$agentCount = $myrow['COUNT'];
	
		// Get Count of Active Supervisor Records matching GUID
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupSupervisorInfo WHERE GUID ='$GUID' AND supervisorStatus = 'ACTIVE'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$supervisorCount = $myrow['COUNT'];

		// Get Count of Active External User Records matching GUID
		$sql = "SELECT COUNT(*) AS COUNT FROM lookupExternalUserInfo WHERE GUID = '$GUID' AND externalUserStatus = 'ACTIVE'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$userCount = $myrow['COUNT'];

		// if a supervisorCount or userCount > 0 then an Active Record exists for one of them, do nothing.
		if ($supervisorCount == 0 && $userCount == 0) { 
			if ($agentCount == 1) {
				// Only agent login exists, set to default agent permission 
				//***************SET THIS SQL PROPERLY TO GRANT DEFAULT AGENT **************************
//				$sql = "UPDATE lookupPermissions SET";
//				$sql .= " permissionname = 'permissionvalue',";
//				$sql .= " permissionname2 = 'permissionvalue2' ";
//				$sql .= "WHERE GUID = '$GUID'";
//				$results = $Database->Query($sql);
				$type = "success";
				if ($_POST['GUID'] == "") {
					$message = "AGENT RECORD SUCCESSFULLY ADDED. [" . $lastName . ", " . $firstName . " (" . strtoupper($attId) . ")]";
				} else {
					$type = "success";
					$message = "AGENT RECORD SUCCESSFULLY UPDATED. [" . $lastName . ", " . $firstName . " (" . strtoupper($attId) . ")]";
				}
				$message .= "<div>DEFAULT AGENT PERMISSIONS WERE GRANTED.</div>";
				$field = "";
			} else {
				// No active logins exist, remove all permissions
				//***************SET THIS SQL PROPERLY TO REMOVE PERMISSIONS **************************
//				$sql = "UPDATE lookupPermissions SET";
//				$sql .= " permissionname = '',";
//				$sql .= " permissionname2 = ''";
//				$sql .= "WHERE GUID = '$GUID'";
//				$results = $Database->Query($sql);
				$type = "success";
				if ($_POST['GUID'] == "") {
					$message = "<div>AGENT RECORD SUCCESSFULLY ADDED. [" . $lastName . ", " . $firstName . " (" . strtoupper($attId) . ")]";
				} else {
					$message = "<div>AGENT RECORD SUCCESSSFULLY UPDATED. [" . $lastName . ", " . $firstName . " (" . strtoupper($attId) . ")]";
				}
				$message .= "<div>NO ACTIVE LOGIN TYPES FOUND. ALL PERMISSIONS WERE REMOVED.</div>";
				$field = "";
			}
		}
	} else {
		// Permission updates were not needed
		if ($_POST['GUID'] == "") {
			$type = "success";
			$message = "AGENT RECORD SUCCESSFULLY ADDED [" . $lastName . ", " . $firstName . " (" . $attId . ")]";
		} else {
			$type = "success";
			$message = "AGENT RECORD SUCCESSFULLY UPDATED [" . $lastName . ", " . $firstName . " (" . strtoupper($attId) . ")]";
			$field = "";
		}
	}

	echoResponse:
	$response = array(
		'type' => $type,
		'message' => $message,
		'field' => $field
	);
	echo json_encode($response);
?>