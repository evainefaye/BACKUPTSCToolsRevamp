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
		echo "		permissions required: ADMINEXTERNALUSER: [ADD], [EDIT], [VIEW], [ALL], [SUPER]<br />\n";
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

	if (isset($userInfo['userPermissions']['misc']['SET_VIEW_RESTRICTED'])) {
		$disabled = "";
	} else {
		$disabled = "DISABLED";	
	}
	
	// Set variable to hide the add button if you don't have ADD or ALL access
	$classHideAddButton = "class='hidden'";
	if ($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "ADD" || $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "ALL" ||$userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "SUPER") {
		$classHideAddButton = "";
	}
	// Set variable to hide the edit selections if you don't have EDIT, VIEW, or ALL access
	$classHideEditSelection = "class='hidden'";
	if ($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "EDIT" || $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "VIEW" || $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "ALL" || $userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "SUPER") {
		$classHideEditSelection = "";
	}

	// Set variable to determine actions available from select list
	$options = "EDIT/VIEW:";
	if ($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'] == "VIEW") {
		$options = "VIEW:";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>EXTERNAL USER ADMINISTRATION</title>
		<!-- Add Stylesheets -->
		<link type='text/css' rel='stylesheet' href='/css/jqx.base.css' />
		<link type='text/css' rel='stylesheet' href='/css/jqx.darkblue.css' />
		<link type='text/css' rel='stylesheet' href='/css/stylesheet.css' />
		
		<!--Retrieve jquery for inclusion-->
		<script type='text/javascript' src='/js/jquery.js'></script>
		<script type="text/javascript" src="/js/jqwidgets/jqx-all.js"></script>
<!--
		<script type="text/javascript" src="/js/jqwidgets/jqxcore.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxbuttons.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxbuttongroup.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxradiobutton.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxnotification.js"></script>
	    <script type="text/javascript" src="/js/jqwidgets/jqxscrollbar.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxlistbox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcombobox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxloader.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcheckbox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxnotification.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxdata.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxtooltip.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxdropdownlist.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxdropdownbutton.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcolorpicker.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxwindow.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxeditor.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcheckbox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxmenu.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxdatetimeinput.js"></script>		
		<script type="text/javascript" src="/js/jqwidgets/jqxcalendar.js"></script>
-->
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
		<div class='center'><h2>EXTERNAL USER ADMINISTRATION</h2></div>
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
			<table style='border-collapse: collapse;' align='center' style='padding: 5px;'>
				<tr>
					<td class='labelAbove' <?php echo $classHideEditSelection ?>>SELECT EXTERNAL USER <?php echo $options ?>&nbsp;&nbsp;</td>
						<td <?php echo $classHideEditSelection ?>></td>
					</td>
					<td class='labelAbove'>TOGGLE SELECTION</td>
				</tr>
				<tr>
					<td <?php echo $classHideEditSelection ?>>
						<div id='selExternalUserId'></div>
					</td>
					<td>
						<div <?php echo $classHideAddButton ?>'>&nbsp;&nbsp;<input type='button' name='btnAddExternalUser' id='btnAddExternalUser' value='ADD EXTERNAL USER' /></div>
					</td>
					<td <?php echo $classHideEditSelection ?>>
						<div>
							<input type = 'button' id='autoComplete' type='button' value='SHOW CLOSEST MATCH' />
						</div>
					</td>
				</tr>
			</table>
			<form name='frmExternalUserAdministration' id='frmExternalUserAdministration'>
				<div id='divExternalUserAdminBody' name='divExternalUserAdminBody'>
					<table class='bordered centered spaced filled'>
						<tr>
							<td class='Record' colspan=2></td>
						</tr>
						<tr>
							<td class='label'>GUID:</td>
							<td class='data GUID'>
								<input class='tooltipHover tooltipFocus' type='text' READONLY name='GUID' id='GUID' size='75' maxlength='36' tooltipText='The externalUser Database Record identifier. This field will eventually be removed.' placeholder='AUTOMATICALLY GENERATED ON SAVE' />
							</td>
						</tr>
						<tr>
							<td class='label'>LAST NAME:</td>
							<td class='data lastName'>
								<input class='changekey forceUpperCase' type='text' name='lastName' id='lastName' size='75' maxlength='50' placeholder='LAST NAME' />
							</td>
						</tr>
						<tr>
							<td class='label'>FIRST NAME:</td><
							<td class='data firstName'>
								<input class='changekey forceUpperCase' type='text' name='firstName' id='firstName' size='75' maxlength='50' placeholder='FIRST NAME' required='required' />
							</td>
						</tr>
						<tr>
							<td class='label'>AT&T ID:</td>
							<td class='data attId'>
								<input class='changekey forceLowerCase' type='text'  name='attId' id='attId' size='75' maxlength='25' placeholder='AT&T ID' required='required' />
							</td>
						</tr>
						<tr>
							<td class='label'>WINDOWS LOGIN:</td>
							<td class='data loginId'>
								<input class='changekey tooltipHover tooltipFocus forceLowerCase' type='text' name='loginId' id='loginId' size='75' maxlength='25' tooltipText='Usually the same as the AT&T ID, but if externalUser logs in with a different login, set it here.' placeholder = 'WINDOWS LOGIN' required='required' />
							</td>
						</tr>
						<tr>
							<td class='label'>E-MAIL ADDRESS:</td>
							<td class='data emailAddress'>
								<input class='changekey forceLowerCase' type='text' name='emailAddress' id='emailAddress' size='75' maxlength='75' placeholder='EMAIL ADDRESS' required='required' />
							</td>
						</tr>
						<tr>
							<td class='data label'>EXTERNAL USER STATUS:</td>
							<td>
								<div id="buttonExternalUserStatus" class='tooltipHover' tooltipText='Inactive externalUsers do not display on drop down lists by default, but data remains.'>
									<button class="buttonGroup" id="ACTIVE">ACTIVE</button>
									<button class="buttonGroup" id="INACTIVE">INACTIVE</button>
								</div>
								<input type='hidden' id='externalUserStatus' name='externalUserStatus' />
							</td>
						</tr>
						<tr>
							<td class='buttons' colspan='2'>
								<input class='inline' type='submit' name='btnSave' id='btnSave' value='SAVE' />&nbsp;&nbsp;<input class='inline' type='button' name='btnCancel' id='btnCancel' value='CANCEL' />
							</td>					
						</tr>
					</table>
				</div>
			</form>
		</div>
	</body>
</html>