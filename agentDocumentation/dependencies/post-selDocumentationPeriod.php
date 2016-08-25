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

	// Get Current Period Information;
	$databasePeriod = date('Y-m-d');
	$displayPeriod = date('M-Y');

	// Get the Oldest documentationPeriod
	$sql = "SELECT TOP 1 documentationPeriod FROM ad_documentationData ORDER BY documentationPeriod ASC";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$oldestPeriod = $myrow['documentationPeriod'];
	$oldestPeriod = new DateTime($oldestPeriod);
	$oldestPeriod  = new DateTime($oldestPeriod->format('Y-m'));

	// Get current Year/Month
	$currentPeriod = new DateTime();
	$currentPeriod = new DateTime($currentPeriod->format('Y-m'));

	while ($currentPeriod >= $oldestPeriod) {
		$documentationMonth[] = array(
			'documentationPeriod' => $currentPeriod->format('Y-m'),
			'displayPeriod' => strtoupper($currentPeriod->format('M Y'))
		);
		$currentPeriod->modify('-1 month');
   }
	echo json_encode($documentationMonth);
?>