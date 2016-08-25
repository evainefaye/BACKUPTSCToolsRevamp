<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	
	// Check Permissions records for user
	$userInfo = $_SESSION['userInfo'];

	if (!isset($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER']) || $getGUID == "") {
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
		echo "		permissions required: ADMINEXTERNALUSER: [ADD], [EDIT], [ALL], [SUPER]<br />\n";
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
	if ($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "ADD") {
		exit;
	}

	// Retrieve ExternalUser Data
	if (!isset($_POST['GUID'])) {
		$GUID = "";
	} else {
		$GUID = $_POST['GUID'];
	}

	if ($GUID != "") {
		$sql = "SELECT * FROM lookupPermissions LEFT JOIN lookupExternalUserInfo ON lookupPermissions.GUID=lookupExternalUserInfo.GUID WHERE lookupPermissions.GUID = '$GUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$externalUserInfo['GUID'] = $GUID;	
		$externalUserInfo['loginId'] = $myrow['loginId'];
		$externalUserInfo['attId'] = $myrow['attId'];
		$externalUserInfo['firstName'] = $myrow['firstName'];
		$externalUserInfo['lastName'] = $myrow['lastName'];
		$externalUserInfo['emailAddress'] = $myrow['emailAddress'];
		$externalUserInfo['externalUserStatus'] = $myrow['externalUserStatus'];
		$externalUserInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$externalUserInfo['ADMINEXTERNALUSER'] = $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'];
	} else {
		$externalUserInfo['GUID'] = $GUID;
		$externalUserInfo['loginId'] = '';
		$externalUserInfo['attId'] = '';
		$externalUserInfo['firstName'] = '';
		$externalUserInfo['lastName'] = '';
		$externalUserInfo['emailAddress'] = '';
		$externalUserInfo['externalUserStatus'] = '';
		$externalUserInfo['userGUID'] = $_SESSION['userInfo']['GUID'];
		$externalUserInfo['ADMINEXTERNALUSER'] = $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'];
	}
	echo json_encode($externalUserInfo);
?>