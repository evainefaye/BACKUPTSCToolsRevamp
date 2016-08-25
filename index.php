<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	// Check Permissions
	$userInfo = $_SESSION['userInfo'];
	$toolsAccess = "";
	if (isset($userInfo['userPermissions']['tools'])) {
		// Check To See if you have any tools permissions set
		foreach ($userInfo['userPermissions']['tools'] as $key => $value) {
			if ($value != "") {
				$toolsAccess = "Yes";
			}
		}
	}
	// Either no values were found on any tools, or tools wasn't set at all.
	if ($toolsAccess == "") {
		$webpage = $_SERVER['PHP_SELF'];
		$hostName = $_SERVER['HTTP_HOST']; 			
		echo "<html>\n";
		echo "	<head>\n";
		echo "		<title>Error!</title>\n";
		echo "	</head>\n";
		echo "	<body>\n";
		echo "		<h3>Error!</h3>\n";
		echo "		instance: $databaseName<br />\n";
		echo "		loginId:  $loginId<br />\n";
		echo "		permissions: any<br />\n";
		echo "		module:  $webpage<br />\n";
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
	if (isset($_COOKIE["lastTool"])) {
		$lastToolURL = $_COOKIE["lastTool"];
		echo "<script type='text/javascript'>\n";
		echo "	location.href='$lastToolURL'";
		echo "</script>\n";
	}
	if ($databaseClass != "") {
		include $_SERVER['DOCUMENT_ROOT'] . "/../includes/getMenu.php";
	}
?>