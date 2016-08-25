<?php
/****************************************************************************************************************
Checks Users Login Information against the lookupAgents, lookupSupervisors, and lookupExternalUsers Databases

If a match is found, then checks lookupPermissions to determine what permissions agent has to tools.

Output:
$_SESSION['userInfo'] - An Array containing user Information

***************************************************************************************************************/


/**************************************************************************************************************
FUNCTION DEFINITIONS
***************************************************************************************************************/

function getUserInfo($requestType, $requestValue) {
	global $Database;
	$sql = "SELECT COUNT(*) AS COUNT FROM lookupPermissions WHERE $requestType = '$requestValue'";
	$results = $Database->Query($sql);
	$myrow = odbc_fetch_array($results);
	$rowCount = $myrow['COUNT'];
	if ($rowCount > 0) {
		$sql= "SELECT * FROM lookupPermissions WHERE $requestType = '$requestValue'";
		$results = $Database->Query($sql);
		$myrow = odbc_fetch_array($results);
		$loginId = $myrow['loginId'];
		$attId = $myrow['attId'];
		$GUID = $myrow['GUID'];
		$firstName = $myrow['firstName'];
		$lastName = $myrow['lastName'];
		$emailAddress = $myrow['emailAddress'];
		$agentInfo = array (
			'loginId' => $loginId,
			'attId' => $attId,
			'GUID' => $GUID,
			'firstName' => $firstName,
			'lastName' => $lastName,
			'emailAddress' => $emailAddress
		);
		// Each Column in Table is a Permission, so set an Array of Permissions
		$sql = "SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = 'lookupPermissions'";
		$results = $Database->Query($sql);
		while ($columnList = odbc_fetch_array($results)) {
			$columnName = $columnList['COLUMN_NAME'];
			$columnNameToUpper = strtoupper($columnName);
			$permissionName = strtoupper(substr($columnNameToUpper, 10)); 
			// If column name begins with 'tool_perm_' then its a tools permission
			if (substr($columnNameToUpper, 0, 10) == 'TOOL_PERM_') {
				$agentInfo['userPermissions']['tools'][$permissionName] = $myrow[$columnName];
			}
			// If column name begins with 'misc_perm_' then its a miscellaneous permission
			if (substr($columnNameToUpper, 0, 10) == 'MISC_PERM_') {
				$agentInfo['userPermissions']['misc'][$permissionName] = $myrow[$columnName];
			}
		}
		$_SESSION['userInfo'] = $agentInfo;
		return $GUID;
	}
	$agentInfo = array (
		'loginId' => '',
		'attId' => '',
		'GUID' => '',
		'firstName' => '',
		'lastName' => '',
		'emailAddress' => ''
	);
	return "";
}

/**************************************************************************************************************
BEGIN MAIN CODE
**************************************************************************************************************/


	// Start a Session to enable getting/setting user information if one is not set
	if (session_id() == '' || !isset($_SESSION)) {
		session_start();
	}
	// Get redirect SESSION value. This controls how the site handles the need to call a redirect when running db_selector
	if (isset($_SESSION['REDIRECT'])) {
		$redirect = $_SESSION['REDIRECT'];
	} else {
		$redirect = '';
	}
	// If redirect == on, then, this was called from db_selector/index, skip redirect handling
	if ($redirect != 'on') {
		// Get session information for database class to use
		if (isset($_SESSION['DATABASE'])) {
			// SESSION is set for DATABASE, compare against COOKIE value if it xists
			$sessionDatabaseClass = $_SESSION['DATABASE'];
			if (isset($_COOKIE['DATABASE'])) {
				$cookieDatabaseClass = $_COOKIE['DATABASE'];
			} else {
				$cookieDatabaseClass = "";
			}
			$cookieDatabaseClass = $_COOKIE['DATABASE'];

			// COOKIE and SESSION DATABASE match, set $databaseClass to the SESSION value
			if ($sessionDatabaseClass == $cookieDatabaseClass) {
				$databaseClass = $sessionDatabaseClass;
			} else {
				// SESSION and COOKIE DATABASE variables did not match. Clear the SESSION, and redirect to db_selector
				header('Location: /db_selector/');
				exit;
			}
		} else {
			// SESSION DATABASE did not exist, get the COOKIE value if it exists
			if (isset($_COOKIE['DATABASE'])) {
				// COOKIE existed, set SESSION to COOKIE
				$_SESSION['DATABASE'] = $_COOKIE['DATABASE'];
				$databaseClass = $_COOKIE['DATABASE'];
			} else {
				// COOKIE DATABASE did not exist did not exist. redirect to db_selector
				unset($_SESSION['REDIRECT']);
				header('Location: /db_selector/');
				exit;
			}
		}
		if ($databaseClass == "") {
			header('Location: /db_selector/');
			exit;
		}
	} else {
		if (isset($_COOKIE['DATABASE'])) {
			if ($_COOKIE['DATABASE'] != "") {
				$databaseClass = $_COOKIE['DATABASE'];
			}
		} else {
			$databaseClass = "";
		}
	}
	if ($databaseClass != "") {
		// Include code to do  PHP Database Operations
		require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/" . $databaseClass);
		// Instantiate the database;
		$Database = new MyDatabase;
		// Connect to the Database
		$Database->Connect();
		if (strpos($_SERVER['REMOTE_USER'], '/') !== false) {
			$cred = explode('/', $_SERVER['REMOTE_USER']);
		}
		if (strpos($_SERVER['REMOTE_USER'], '\\') !== false) {
			$cred = explode('\\', $_SERVER['REMOTE_USER']);
		} 
		// split the domain and the user ID.  if further security is required, we can add the domain wayad as a check as well.
		if (strpos($_SERVER['REMOTE_USER'], '\\') !== false || strpos($_SERVER['REMOTE_USER'], '/') !== false) {
			if (count($cred) == 1) {
				array_unshift($cred, "");
			}
			list($domain, $loginId) = $cred;
		} else {
			$loginId=$_SERVER['REMOTE_USER'];
		}

		// Call the getUserInfo function by loginId
		$getGUID = getUserInfo('loginId',$loginId);
		$uri = $_SERVER['REQUEST_URI'];

		// If you are not at /db_selector/, with no permissions record, throw an error
		if (!strpos($uri,'db_selector') && $getGUID == "") {
			$webpage = $_SERVER['PHP_SELF'];
			$hostName = $_SERVER['HTTP_HOST']; 
			echo "<html>\n";
			echo "	<head>\n";
			echo "		<title>Error!</title>\n";
			echo "	</head>\n";
			echo "	<body>\n";
			echo "		<h3>Error!</h3>\n";
			echo "		instance: " . strtoupper(strtok($databaseClass,'.')) . "<br />\n";
			echo "		loginId:  $loginId<br />\n";
			echo "		permissions: <br />\n";
			echo "		Error:  No record found in lookupPermissions for loginId = $loginId<br />\n";
			echo "		module: $webpage<br />\n";
			echo "		<br />\n";
			echo "		You may click <a href='http://$hostName/db_selector/'>HERE</a> to switch database instances if necessary.\n";
			echo "	</body>\n";
			echo "</html>\n";
			exit;
		}
/*	
		// Routines to run only once per user session (initial login in a new browser)
		if (!isset($_SESSION['runonce'])) {
	
			//***************************************************************************************************
			// Create and Email Notification Emails for followup dates that are today or older
			$dte = date('Y-m-d');
			$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentation_data LEFT JOIN lookupAgents ON ad_documentation_data.agent_id=lookupAgents.agentGUID LEFT JOIN lookup_supervisors ON lookupAgents.supervisorGUID=lookup_supervisors.supervisor_id WHERE followup_date <= '$dte' AND followup_date != ''";
			$results_main = $Database->Query($sql);
			$myrow = odbc_fetch_array($results);
			$rowCount = $myrow['COUNT'];
			if ($rowCount > 0) {
				// Load Mailer LIbraries
				require_once '/js/swift_mailer/swift_required.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . "/../includes/config.php";
				//setup email selection
				$transport = Swift_SmtpTransport::newInstance()
					->setHost($server_name)
					->setPort($server_port)
					->setUsername($email_user_name)
					->setPassword($email_user_pass);
				$mailer = Swift_Mailer::newInstance($transport);
				$current_supervisor_name= $myrow_main['supervisor_name'];
				$current_supervisor_email = $myrow_main['supervisor_email'];
				$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentation_data LEFT JOIN lookupAgents ON ad_documentation_data.agent_id=lookupAgents.agentGUID LEFT JOIN lookup_supervisors ON lookupAgents.supervisorGUID=lookup_supervisors.supervisor_id WHERE followup_date <= '$dte' AND followup_date != ''";
				$results_main = $Database->Query($sql);
				while ($myrow_main = odbc_fetch_array($results_main)) {
					$selAgentId = $myrow_main['agent_id'];
					$selDocumentationMonth = $myrow_main['documentation_month'];
					// convert this to text
					$selDocumentationMonthText = $myrow_main['documentation_month'];
					$documentationId = $myrow_main['documentation_id'];
					$body = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'>";
					$body .= "A followup date was selected for the following agent documentation that is now due.<br /><br />";
					$currentDateView = date('m/d/Y');
					$currentDateSave = date('Y-m-d');
					$timestamp = date('m/d/Y h:i:s');
					$sql = "SELECT * FROM lookupAgents LEFT JOIN lookup_supervisors ON lookupAgents.supervisorGUID=lookup_supervisors.supervisor_id WHERE agent_id='$selAgentId'";
					$results = $Database->Query($sql);
					while ($myrow = odbc_fetch_array($results)) {
						$agent_name = $myrow['agentName'];
						$agent_notes = $myrow['agentNotes'];
						$disciplinary_step = $myrow['disciplinary_step'];
						$supervisor_name = $myrow['supervisor_name'];
						$supervisor_id = $myrow['supervisor_id'];		
					}
					$sql = "SELECT * FROM ad_scorecard_results WHERE documentation_month='$selDocumentationMonth' AND agent_id='$selAgentId'";
					$results = $Database->Query($sql);
					while ($myrow = odbc_fetch_array($results)) {
						$scorecard_score = $myrow['scorecard_score'];
					}
					if ($scorecard_score == "") {
						$scorecard_score="Undetermined";
					}
					$sql = "SELECT TOP 1 date_of_documentation FROM ad_documentation_data WHERE documentation_month <= '$selDocumentationMonth' AND agent_id='$selAgentId' ORDER BY date_of_documentation DESC";
					$results = $Database->Query($sql);
					while ($myrow = odbc_fetch_array($results)) {
						$last_documentation_date = $myrow['date_of_documentation'];
					}
					if ($last_documentation_date == "") {
						$last_documentation_date="Undetermined";
					}
					if ($last_documentation_date != "Undetermined") {
						$exploded_last_documentation_date = explode("-", $last_documentation_date);
						$last_documentation_date = $exploded_last_documentation_date[1] . "/" . $exploded_last_documentation_date[2] . "/" . $exploded_last_documentation_date[0];
					}
					$sql = "SELECT * FROM ad_minimum_documentations_data WHERE agent_id='$selAgentId' AND documentation_month='$selDocumentationMonth'";
					$results = $Database->Query($sql);
					while ($myrow= odbc_fetch_array($results)) {
						$minimum_documentations = $myrow['minimum_documentations'];
					}
					if ($minimum_documentations == "") {
						$minimum_documentations='Undetermined';
					}
					$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentation_data WHERE documentation_month='$selDocumentationMonth' AND agent_id='$selAgentId'";
					$results = $Database->Query($sql);
					$myrow = odbc_fetch_array($results);
					$completed_documentations = $myrow['COUNT'];
					$sql = "SELECT * FROM ad_documentation_data WHERE documentation_id='$documentationId'";
					$results = $Database->Query($sql);
					$myrow = odbc_fetch_array($results);
					$date_of_documentation = $myrow['date_of_documentation'];
					$documentation_type_id = $myrow['documentation_type_id'];
					$quick_reference = $myrow['quick_reference'];
					$documentation_note = $myrow['documentation_note'];
					$documentation_note = nl2br($documentation_note);
					$agent_comments = $myrow['agent_comments'];
					$agent_comments = trim($agent_comments);
					$agent_comments = nl2br($agent_comments);
					$entered_by = $myrow['completed_by'];
					$followup_date = $myrow['followup_date'];
					if ($followup_date != "") {
						$exploded_followup_date = explode('-', $followup_date);
						$followup_date = $exploded_followup_date[1] . "/" . $exploded_followup_date[2] . "/" . $exploded_followup_date[0];
					} else {
						$followup_date = "&nbsp;";
					}	
					$sql2 = "SELECT COUNT(*) AS COUNT FROM lookup_external_users WHERE user_id='$entered_by'";
					$results2 = $Database->Query($sql);
					$myrow2 = odbc_fetch_array($results2);
					$rowCount = $myrow2['COUNT'];
					if ($rowCount > 0) {
						$sql2 = "SELECT * FROM lookup_external_users WHERE user_id='$entered_by'";
						$results2 = $Database->Query($sql);
						$myrow2 = odbc_fetch_array($results2);
						$entered_by = $myrow2['user_name'];
						$entered_by_email = $myrow2['user_email'];
					} else {
						$sql2 = "SELECT COUNT(*) AS COUNT FROM lookup_supervisors WHERE supervisor_id='$entered_by'";
						$results2 = $Database->Query($sql2);
						$myrow2 = odbc_fetch_array($results2);
						$rowCount = $myrow['COUNT'];
						if ($rowCount > 0) {
							$sql2 = "SELECT * AS COUNT FROM lookup_supervisors WHERE supervisor_id='$entered_by'";
							$results2 = $Database->Query($sql2);
							$myrow2 = odbc_fetch_array($results2);
							$entered_by = $myrow2['supervisor_name'];
							$entered_by_email = $myrow2['supervisor_email'];
						} else {
							$sql2 = "SELECT COUNT(*) AS COUNT FROM lookupAgents WHERE attId='$entered_by'";
							$results2 = $Database->Query($sql2);
							$myrow2= odbc_fetch_array($results2);
							$rowCount = $myrow2['COUNT'];
							if ($rowCount > 0) {
								$sql2 = "SELECT * AS COUNT FROM lookupAgents WHERE attId='$entered_by'";
								$results2 = $Database->Query($sql2);
								$myrow2 = odbc_fetch_array($results2);
								$entered_by = $myrow2['agentName'];
								$entered_by_email = $myrow2['agentEmail'];
							} else {
								$entered_by = 'Unknown User';
								$entered_by_email = '';
							}
						}
					}
					$sql2 = "SELECT * FROM ad_lookup_documentation_type WHERE documentation_type_id='$documentation_type_id'";
					$results2 = $Database->Query($sql2);
					$myrow2 = odbc_fetch_array($results2);
					$documentation_type_description = $myrow2['documentation_type_description'];
					$body .= "			<table align='center' rules='none' frame='box' style='width: 80%; spacing: 0px;  border: 1px solid #000000; border-collapse: collapse;'>\n";
					$body .= "				<thead>\n";
					$body .= "				</thead>\n";
					$body .= "				<tbody>\n";
					$body .= "					<tr>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Agent Name:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%''>$agent_name</td>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Scorecard Score:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$scorecard_score</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Agent ID:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$selAgentId</td>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Most Recent Documentation:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$last_documentation_date</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Supervisor:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;'>\n";
					$body .= "							<div id=supervisordisplay name=supervisordisplay>$supervisor_name</div>\n";
					$body .= "						</td>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Minimum Required Documentation:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$minimum_documentations</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Disciplinary Step:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$disiplinary_step</td>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Completed Documentations:</td>\n";
					$body .= "						<td style='font-weight: bold; vertical-align: top;' width='25%'>$completed_documentations</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr class='noPrint'>\n";
					$body .= "						<td style='text-align: right; vertical-align: top;' width='25%'>Agent Notes:</td>\n";
					$body .= "						<td colspan='3'>\n";
					$body .= "							<div id=notesdisplay name=notesdisplay>$agent_notes</div>\n";
					$body .= "						</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr class='noPrint'>\n";
					$body .= "						<td>&nbsp;</td>\n";
					$body .= "					</tr>\n";
					$body .= "					<tr>\n";
					$body .= "						<td  style='background-color: #cccccc; border-top: #000000 2px solid; border-bottom: #000000 2px solid; font-size: 80%; font-weight: bold; text-transform: uppercase;' colspan='4' align='center' valign='center'>AGENT DOCUMENTATION ($selDocumentationMonthText)</td>\n";
					$body .= "					</tr>\n";
					$body .= "				</tbody>\n";
					$body .= "				<tfoot>\n";
					$body .= "				</tfoot>\n";
					$body .= "			</table>\n";
					$body .= "			<h3><p style='text-align: center; text-transform: uppercase;'>$documentation_type_description</p></h3>\n";
					$body .= "			<table align='center' border=1 frame='box' id='tblDocumentationData' name='tblDocumentationData' style='width: 80%; spacing: 0px;  border: 1px solid #000000; border-collapse: collapse;'>\n";
					$body .= "				<thead>\n";
					$body .= "					<tr>\n";
					if ($quick_reference != "") {
						$body .= "						<th colspan=4 style='padding-left: 1px; border-style: solid; border-color: black; border-width: 1px; font-family: helvetica, arial, sans serif; padding-left: 1px; font-weight: bold;'>DOCUMENTATION REFERENCE<br />$quick_reference</th>\n";
					} else {
						$body .= "						<th colspan=4 style='padding-left: 1px; border-style: solid; border-color: black; border-width: 1px; font-family: helvetica, arial, sans serif; padding-left: 1px; font-weight: bold;'>&nbsp;</th>\n";
					}
					$body .= "					</tr>\n";
					$body .= "					<tr>\n";
					$body .= "						<th align='center' width='15%' STYLE='border-bottom: #000000 2px solid; border-top: #000000 2px solid;'>Documentation Date</th>\n";
					$body .= "						<th align='center' width='15%' STYLE='border-bottom: #000000 2px solid; border-top: #000000 2px solid;'>Entered By</th>\n";
					$body .= "						<th align='center' width='15%' STYLE='border-bottom: #000000 2px solid; border-top: #000000 2px solid;'>Followup Date</th>\n";	
					$body .= "						<th align='center' width='60%' STYLE='border-bottom: #000000 2px solid; border-top: #000000 2px solid;'>Documentation</th>\n";
					$body .= "					</tr>\n";
					$body .= "				</thead>\n";
					$body .= "				<tbody>\n";
					$body .= "					<tr>\n";
					unset($exploded_date_of_documentation);
					$exploded_date_of_documentation = explode("-", $date_of_documentation);
					$date_of_documentation = $exploded_date_of_documentation[1] . "/" . $exploded_date_of_documentation[2] . "/" . $exploded_date_of_documentation[0];
					$body .= "						<td align='center' valign='center'>$date_of_documentation</td>\n";
					$body .= "						<td align='left' valign='center'>$entered_by</td>\n";
					$body .= "						<td align='center' valign='center'>$followup_date</td>\n";	
					$body .= "						<td style='padding-left: 10px; padding-right: 10px;' valign='Top'>\n";
					$body .= "							$documentation_note\n";
					$body .= "						</td>";
					$body .= "					</tr>\n";
					$sql = "SELECT COUNT(*) AS COUNT FROM ad_documentation_subnotes WHERE documentation_id='$documentationId'";
					$results = $Database->Query($sql);
					$myrow=odbc_fetch_array($results);
					$rowCount = $myrow['COUNT'];
					if ($rowCount > 0) {
						$sql = "SELECT * FROM ad_documentation_subnotes WHERE documentation_id='$documentationId' ORDER BY entered_date ASC";
						$results = $Database->Query($sql);
						while ($myrow = odbc_fetch_array($results)) {
							$entered_date = $myrow['entered_date'];
							unset($exploded_entered_date);
							$exploded_entered_date = explode("-", $entered_date);
							$entered_date = $exploded_entered_date[1] . "/" . $exploded_entered_date[2] . "/" . $exploded_entered_date[0];
							$entered_by = $myrow['entered_by'];
							$subnote = $myrow['subnote'];
							$subnote = nl2br($subnote);
							unset($external_user);
							unset($supervisor_user);
							unset($agent_user);
							$sql2 = "SELECT COUNT(*) AS COUNT FROM lookup_external_users WHERE user_id = '$entered_by'";
							$results2 = $Database->Query($sql2);
							$myrow = odbc_fetch_array($results2);
							$rowCount = $myrow['COUNT'];
							if ($rowCount > 0) {
								$sql2 = "SELECT * AS COUNT FROM lookup_external_users WHERE user_id = '$entered_by'";
								$results2 = $Database->Query($sql2);
								$myrow2 = odbc_fetch_array($results2);
								$entered_by = $myrow2['user_name'];
								$entered_by_email = $myrow2['user_email'];
							} else {
								$sql2 = "SELECT COUNT(*) FROM lookup_supervisors WHERE supervisor_id = '$entered_by'";
								$results2 = $Database->Query($sql2);
								$myrow = odbc_fetch_array($results2);
								$rowCount = $myrow['COUNT'];
								if ($rowCount > 0) {
									$sql2 = "SELECT * FROM lookup_supervisors WHERE supervisor_id = '$entered_by'";
									$results2 = $Database->Query($sql2);
									$myrow2 = odbc_fetch_array($results2);
									$entered_by = $myrow2['supervisor_name'];
									$entered_by_email = $myrow2['supervisor_email'];
								} else {
									$sql2 = "SELECT COUNT(*) FROM lookupAgents WHERE attId = '$entered_by'";
									$results2 = $Database->Query($sql2);
									$myrow = odbc_fetch_array($results);
									$rowCount = $myrow['COUNT'];
									if ($rowCount > 0) {
										$sql2 = "SELECT * FROM lookupAgents WHERE attId = '$entered_by'";
										$results2 = $Database->Query($sql2);
										$myrow2 = odbc_fetch_array($results2);
										$entered_by = $myrow2['agentName'];
										$entered_by_email = $myrow2['agentEmail'];
									} else {
										$entered_by = 'Unknown User';
									}
								}
							}
							$body .= "					<tr>\n";
							$body .= "						<td align='center' valign='center' style='border-top-style: none;'>$entered_date</td>";
							$body .= "						<td align='left' valign='center' style='border-top-style: none;'>$entered_by</td>\n";
							$body .= "						<td align='left' valign='center' style='border-top-style: none;'>&nbsp;</td>\n";			
							$body .= "						<td align='left' valign='center' style='padding-left: 10px; padding-right: 10px; border-top-style: none;'>$subnote</td>\n";
							$body .= "					</tr>\n";
						}
					}
					if ($agent_comments != "") {
							$body .= "					<tr>\n";
							$body .= "						<td align='center' valign='center' style='border-top-style: none;'>&nbsp;</td>";
							$body .= "						<td align='left' valign='center' style='border-top-style: none;'>&nbsp;</td>\n";
							$body .= "						<td align='left' valign='center' style='border-top-style: none;'>Agent Comments:</td>\n";			
							$body .= "						<td align='left' valign='center' style='padding-left: 10px; padding-right: 10px; border-top-style: none;'>$agent_comments</td>\n";
							$body .= "					</tr>\n";
					}
					$body .= "				</tbody>\n";
					$body .= "				<tfoot>\n";
					$body .= "				</tfoot>\n";
					$body .= "			</table>\n";
					$body .= "			<table align='center'>\n";
					$body .= "				<thead>\n";
					$body .= "				</thead>\n";
					$body .= "				<tbody>\n";
					$body .= "					<tr>\n";
					$body .= "						<td>\n";
					$body .= "						</td>\n";
					$body	 .= "					</tr>\n";
					$body .= "				</tbody>\n";
					$body .= "				<tfoot>\n";
					$body .= "				</tfoot>\n";
					$body .= "			</table>\n";
					$body .= "		</form>\n";
					$body .= "	</body>";
					$body .= "</html>";
					$subject = "Agent Documentation Followup Notification - $agent_name - Entry Date: $date_of_documentation";
					if ($entered_by_address == $current_supervisor_email) {
						$message = Swift_Message::newInstance()
							->setFrom(array($email_from_address => $email_from))	
							->setTo(array($entered_by_email => $entered_by))
							->setSubject($subject)
							->setBody($body,'text/html');
						$mailer->send($message);
					} else {
						$message = Swift_Message::newInstance()
							->setFrom(array($email_from_address => $email_from))
							->setTo(array($supervisor_email => $supervisor_name))
							->setCc(array($entered_by_email => $entered_by))
							->setSubject($subject)
							->setBody($body,'text/html');
					}
				}
				$sql = "UPDATE ad_documentation_data SET followup_date='' WHERE followup_date !='' AND followup_date <= '$dte'";
				$results = $Database->Query($sql);
			}
		
//*********************************************************************************************
			// Create Current Team History Assignment for Month if it does not already exist

			$dte= date('Y-m');
			$sql = "SELECT COUNT(*) AS COUNT FROM lookup_team_history WHERE period='$dte'";
			$results = $Database->Query($sql);
			$myrow = odbc_fetch_array($results);
			$rowCount = $myrow['COUNT'];
			if ($rowCount < 1) {
				$sql = "SELECT COUNT(*) AS COUNT FROM lookupAgents WHERE active = 'Y'";
				$results = $Database->Query($sql);
				$myrow = odbc_fetch_array($results);
				$rowCount = $myrow['COUNT'];
				if ($rowCount > 0) {
					$sql = "SELECT * AS COUNT FROM lookupAgents WHERE active = 'Y'";
					$results = $Database->Query($sql);
					while ($myrow = odbc_fetch_array($results)) {
						$agent_id = $myrow['attId'];
						$supervisor_id = $myrow['supervisor_id'];
						$sql2 = "INSERT INTO lookup_team_history (period,agent_id,supervisor_id) VALUES ('$dte','$agent_id','$supervisor_id')";
						$results2 = $Database->Query($sql2);
					}
				}
			}		
		}
		$_SESSION['runonce'] = 'completed';
	*/
	} else {
		if ($redirect != 'on') {
			unset($_SESSION['redirect']);
			header('Location: /db_selector/');
			exit;
		}
	}
?>