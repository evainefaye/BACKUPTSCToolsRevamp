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
	$options = "";
	if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION']== "ADD" || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == "ALL") {
		$options .= "ADD";
	}

	if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == "EDIT" || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == "ALL") {
		if ($options != "") {
			$options .= ", ";
		}
		$options .= "EDIT, VIEW";
	}

	if ($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == "VIEW" || $userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'] == "SELF") {
		if ($options != "") {
			$options .= ", ";
		}
		$options .= "VIEW";
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>AGENT DOCUMENTATION</title>
		<!-- Add Stylesheets -->
		<link type='text/css' rel='stylesheet' href='/css/jqx.base.css' />
		<link type='text/css' rel='stylesheet' href='/css/jqx.darkblue.css' />
		<link type='text/css' rel='stylesheet' href='/css/stylesheet.css' />

		<!--Retrieve jquery for inclusion-->
		<script type='text/javascript' src='/js/jquery.js'></script>
		<script type="text/javascript" src="/js/jqwidgets/jqx-all.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/globalization/globalize.js"></script>		
		<script type='text/javascript' src='/js/jquery.form.js'></script>
		<script type='text/javascript' src='/js/jquery.cookie.js'></script>
		<script type='text/javascript' src='/js/jquery.moment.js'></script>
		<script type='text/javascript' src='js/javascript.js'></script>
	</head>
	<body>
<?php
	if ($databaseClass != "") {
		include $_SERVER['DOCUMENT_ROOT'] . "/../includes/getJQXMenu.php";
	}
?>	
		<div align='center'><h2>AGENT DOCUMENTATION</h2></div>
		<div align='center'><h4><?php echo $options ?> DOCUMENTATION</h2></div>
		<div id='formLoad'></div>
		<div id='messageNotification'></div>
		<div id='confirmationWindow'>
			<div>WARNING - UNSAVED DATA EXISTS!</div>
			<div>
				<div style='font-weight: bold;padding: 25px;'>Unsaved data/changes exist.  Continuing will result in the loss of changes.<br /><br />Do you want to continue without saving data?<br /></div>
				<div style="float: right; margin-top: 15px;">
					<input type="button" id="yes" value="YES" style="margin-right: 10px" />
					<input type="button" id="no" value="NO" style="margin-right: 25px;" />
				</div>
			</div>
		</div>
		<div id='formWrapper'>
			<div id='divDocumentationSelections'>
				<form name='frmAgentDocumentationSelections' id='frmAgentDocumentationSelections'>
					<table class='bordered centered filled spaced fixed90pct'>
						<tr>
							<td class='labelAbove'>CURRENT SUPERVISOR<BR /><i>(optional)</i></td>
							<td class='labelAbove'>AGENT NAME</td>
							<td class='labelAbove'>DOCUMENTATION<BR />PERIOD</td>
							<td class='labelAbove'>TOGGLE<BR />SELECTION</td>
						</tr>
						<tr>
							<td>
								<div class='center inline' style="margin-left: 25px;float:left;" id='selSupervisorId'></div>
								<img class='invisible' style="margin-left: 5px;float: left;" id='clearSupervisor' src='/images/clearField.png' />
							</td>
							<td>
								<div class='center' id='selAgentId'></div>
							</td>
							<td>
								<div class='center' id='selDocumentationPeriod'></div>
							</td>
							<td <?php echo $classHideEditSelection ?>>
								<div class='center'>
									<input type = 'button' id='autoComplete' type='button' value='SHOW CLOSEST MATCH' />
								</div>
								<div class='center'>
									<input type = 'button' id='includeInactive' type='button' value='INCLUDE INACTIVE AGENTS' />
								</div>
							</td>
						</tr>					
					</table>
				</form>
			</div>
			<br />
			<div name='divDocumentationHeader' id='divDocumentationHeader'></div>
		</div>
	</body>
</html>