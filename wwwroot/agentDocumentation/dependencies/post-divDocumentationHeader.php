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

	// Safeguard if there is no selAgentID and no selDocumentationPeriod set, this is being called incorrectly close out and do nothing
	if (!isset($_POST['agentGUID']) || !isset($_POST['documentationPeriod'])) {
		exit;
	}

	// Set flag for edit agent permissions if you have them and are not editing your own record.
	$editAgent = false;
	if (isset($userInfo['userPermissions']['tools']['ADMINAGENT']) || $getGUID == "") {
		if (($userInfo['userPermissions']['tools']['ADMINAGENT'] == "EDIT" || $userInfo['userPermissions']['tools']['ADMINAGENT'] == "ALL" || $userInfo['userPermissions']['tools']['ADMINAGENT'] == "SUPER") && $agentGUID != $userInfo['GUID']) {
			$editAgent = true;
		}
	}
	
	// Initialize Passed Information
	$agentGUID=$_POST['agentGUID'];
	$documentationPeriod=$_POST['documentationPeriod'];

	// Get Agent Information
	$sql = "SELECT * FROM lookupPermissions LEFT JOIN lookupAgentInfo ON lookupPermissions.GUID = lookupAgentInfo.GUID WHERE lookupPermissions.GUID='$agentGUID'";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$agentFirstName = $myrow['firstName'];
	$agentLastName = $myrow['lastName'];
	$agentAttId = $myrow['attId'];
	$agentDisplayName = $agentLastName . ", " . $agentFirstName;
	$agentNotes = $myrow['agentNotes'];
	$disciplinaryStep = $myrow['disciplinaryStep'];
	$requireViewRestricted = $myrow['requireViewRestricted'];
	$hireDate = $myrow['hireDate'];
	$agentType = $myrow['agentType'];
	$agentStatus = $myrow['agentStatus'];
	$supervisorGUID = $myrow['supervisorGUID'];
	if ($supervisorGUID != '') {
		$sql = "SELECT * FROM lookupPermissions WHERE GUID='$supervisorGUID'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$supervisorFirstName = $myrow['firstName'];
		$supervisorLastName = $myrow['lastName'];
		$supervisorAttId = $myrow['attId'];
		$supervisorDisplayName = $supervisorLastName . ", " . $supervisorFirstName;
	} else {
		$supervisorFirstName = '';
		$supervisorLastName = 'NONE';
		$supervisorAttId = '';
		$supervisorDisplayName = $supervisorLastName;
	}
	$sql = "SELECT TOP 1 documentationDate FROM ad_documentationData WHERE agentGUID='$agentGUID' ORDER BY documentationDate DESC";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$getDate = $myrow['documentationDate'];
	if ($getDate == '') {
		$mostRecentDocumentationDate = 'NONE';
	} else {
		$formatDate = new DateTime($getDate);
		$mostRecentDocumentationDate = strtoupper($formatDate->format('F j, Y'));
	}
	$formatDate = new DateTime($documentationPeriod);
	$documentationPeriodDisplay = strtoupper($formatDate->format('F Y'));
	$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentationData WHERE documentationPeriod='$documentationPeriod' AND agentGUID='$agentGUID' AND ad_documentationData.documentationStatus='ACTIVE'";
	$results = $Database->Query($sql);
	$myrow=odbc_fetch_array($results);
	$completedDocumentationCount=$myrow['COUNT'];
	echo "		<table table class='bordered centered filled spaced fixed90pct'>\n";
	echo "			<thead>\n";
	echo "			</thead>\n";
	echo "			<tbody>\n";
	echo "				<tr>\n";
	echo "					<td class='label' width='25%'>AGENT NAME:</td>\n";
	echo "					<td class='datafield' width='25%'>$agentDisplayName</td>\n";
	echo "					<td class='label' width='25%'>AT&T ID:</td>\n";
	echo "					<td class='datafield' width='25%'>$agentAttId</td>\n";
	echo "				</tr>\n";
	echo "				<tr>\n";
	echo "					<td class='label' width='25%'>CURRENT SUPERVISOR:</td>\n";
	if (!$editAgent) {
		echo "					<td class='datafield' width='25%'>$supervisorDisplayName</td>\n";
	} else {
		echo "					<td class='datafield' width='25%'>\n";
		echo "						<div class='hidden' id='supervisorGUID'>$supervisorGUID</div>\n";
		echo "						<select id='editSupervisor' name='editSupervisor'>\n";
		if ($supervisorGUID != "") {
			$supervisorGUID = " OR lookupSupervisorInfo.GUID='" . $supervisorLookupGUID . "'";
		} else {
			$supervisorLookupGUID = "";
		}
	
		// Get list of active supervisors from lookupSupervisorInfo along with the agents current Supervisor (if inactive), generate JSON with GUID and LastName
		$sql = "SELECT * FROM lookupSupervisorInfo LEFT JOIN lookupPermissions ON lookupSupervisorInfo.GUID=lookupPermissions.GUID WHERE lookupPermissions.GUID IS NOT NULL AND (supervisorStatus='ACTIVE' $supervisorLookupGUID) ORDER BY lastName, firstName ASC";
		$results = $Database->Query($sql);
		while ($myrow = odbc_fetch_array($results)) {
			$supervisorGUID = $myrow['GUID'];
			$supervisorFirstName = $myrow['firstName'];
			$supervisorLastName = $myrow['lastName'];
			$supervisorAttId = $myrow['attId'];
			$supervisorFullName = $supervisorLastName . ", " . $supervisorFirstName;
			$supervisorDisplayName = $supervisorFullName . " [" . $supervisorAttId . "]";
			$supervisorStatus = $myrow['supervisorStatus'];
			echo "							<option value='$supervisorGUID'>$supervisorDisplayName</option>\n";
		}
		echo "						</select>\n";
		echo "					</td>\n";
	}
	echo "					<td class='label' width='25%'>MOST RECENT DOCUMENTATION:</td>\n";
	echo "					<td class='datafield' width='25%'>$mostRecentDocumentationDate</td>\n";
	echo "				</tr>\n";
	echo "				<tr>\n";
	echo "					<td class='label' width='25%'>DISCIPLINARY STEP:</td>\n";
	if (!$editAgent) {
		echo "					<td class='datafield' width='25%'>$disciplinaryStep</td>\n";
	} else {
		echo "					<td class='datafield' width='25%'>\n";
		echo "						<div class='hidden' id='disciplinaryStep'>$disciplinaryStep</div>\n";
		echo "						<select id='editDisciplinaryStep' name='editDisciplinaryStep'>\n";
		echo "							<option value='NONE'>NONE</option>\n";
		echo "							<option value='VERBAL WARNING'>VERBAL WARNING</option>\n";
		echo "							<option value='WRITTEN WARNING'>WRITTEN WARNING</option>\n";
		echo "							<option value='FINAL WRITTEN WARNING'>FINAL WRITTEN WARNING</option>\n";
		echo "							<option value='CAP'>CAP</option>\n";
		echo "							<option value='PREPIP'>PREPIP</option>\n";
		echo "							<option value=''>PIP</option>\n";
		echo "						</select>\n";
		echo "					</td>\n";
	}
	echo "					<td class='label' width='25%'>PERIOD DOCUMENTATION COUNT:</td>\n";
	echo "					<td class='datafield' width='25%'>$completedDocumentationCount</td>\n";
	echo "				</tr>\n";
	if (!$editAgent) {
		echo "				<tr>\n";
		echo "					<td class='label' width='25%'>AGENT NOTES:</td>\n";
		echo "					<td class='bordered' colspan='2'>$agentNotes</td>\n";
		echo "				<tr>\n";
	} else {
		echo "				<tr>\n";
		echo "					<td colspan='4' class='labelAbove'>AGENT NOTES</td>\n";
		echo "				</tr>\n";
		echo "				<tr>\n";
		echo "					<td colspan='4'>\n";
		echo "						<div class='editable' contenteditable='true' id='editAgentNotes'>$agentNotes</div>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
	}
	$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentationData WHERE documentationPeriod='$documentationPeriod' AND ad_documentationData.agentGUID='$agentGUID' AND ad_documentationData.documentationStatus='ACTIVE'";
	$results = $Database->Query($sql);
	$result = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$count = $myrow['COUNT'];
	if ($count == 0 && ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] != 'ADD' && $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] != 'ALL')) {
		echo "				<tr>\n";
		echo "					<td class='bold center' colspan='4'>\n";
		echo "						<h2>NO DOCUMENTATION FOUND FOR $agentDisplayName - $documentationPeriodDisplay</h2>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
	} else {
		echo "				<tr>\n";
		echo "					<td class='bold center' colspan='4'><h2>DOCUMENTATION FOR $agentDisplayName - $documentationPeriodDisplay</h2></td>\n";
		echo "				</tr>\n";
		echo "				<tr>\n";
		echo "					<td colspan='4' align='center'>\n";
		echo "						<table name='tblDocumentationSummary' id='tblDocumentationSummary'>\n";
		echo "							<thead>\n";
		echo "								<tr>\n";
		echo "									<th>ACTION1</th>\n";
		echo "									<th>ACTION2</th>\n";
		echo "									<th>ACTION3</th>\n";
		echo "									<th>GUID</th>\n";
		echo "									<th>DATE</th>\n";
		echo "									<th>CREATED BY</th>\n";
		echo "									<th>TYPE</th>\n";
		echo "									<th>REFERENCE</th>\n";	
		echo "								</tr>\n";
		echo "							</thead>\n";
		echo "							<tbody>\n";
		$sql = "SELECT ad_DocumentationData.GUID, agentGUID, lastUpdateDate, lastUpdateBy, documentationPeriod, documentationDate, completedBy, documentationTypeGUID, quickReference, documentationNote, agentComments, followUpDate, documentationType FROM ad_documentationData LEFT JOIN ad_lookupDocumentationType ON ad_documentationData.documentationTypeGUID=ad_lookupDocumentationType.GUID WHERE documentationPeriod='$documentationPeriod' AND ad_documentationData.agentGUID='$agentGUID' AND ad_documentationData.documentationStatus='ACTIVE' ORDER BY documentationDate ASC";
		$results = $Database->Query($sql);
		while ($myrow = odbc_fetch_array($results)) {
			$documentationGUID = $myrow['GUID'];
			$getDate = $myrow['documentationDate'];
			$formatDate = new DateTime($getDate);
			$documentationDate = strtoupper($formatDate->format('m/d/Y'));
			$documentationType = $myrow['documentationType'];
			$quickReference = $myrow['quickReference'];
			$completedByGUID = $myrow['completedBy'];
			if ($completedByGUID != "") {
				$sql2 = "SELECT * FROM lookupPermissions WHERE GUID='$completedByGUID'";
				$results2 = $Database->Query($sql2);
				$myrow2 = odbc_fetch_array($results2);
				$originatingSupervisor = $myrow2['lastName'] . ", " . $myrow2['firstName'];
			} else {
				$originatingSupervisor = "UNKNOWN";
			}
			echo "								<tr>\n";
			if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'VIEW' || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'EDIT' || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'ALL' || ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'SELF' && $agentGUID == $userInfo['GUID'])) {
				echo "									<td>VIEW</td>\n";
			} else {
				echo "									<td></td>\n";
			}
			if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'EDIT' || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'ALL') {
				echo "									<td>EDIT</td>\n";
			} else {
				echo "									<td></td>\n";
			}
			if (($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'EDIT' || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'ALL') && ($completedByGUID == $userInfo['GUID'] && $agentGUID != $completedByGUID)) {
				echo "									<td>DELETE</td>\n";
			} else {
				echo "									<td></td>\n";
			}
			echo "									<td>$documentationGUID</td>\n";
			echo "									<td>$documentationDate</td>\n";
			echo "									<td>$originatingSupervisor</td>\n";
			echo "									<td>$documentationType</td>\n";
			echo "									<td>$quickReference</td>\n";			
			echo "								</tr>\n";
		}
		if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'ADD' || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == 'ALL') {
			echo "								<tr>\n";
			echo "									<td>ADD</td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";
			echo "									<td></td>\n";			
			echo "								</tr>\n";
		}
		echo "							</tbody>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
	}
	echo "			</tbody>\n";
	echo "		</table>\n";
	echo "	</body>\n";
	echo "</html>\n";
?>