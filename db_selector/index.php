<?php
	session_start();
	$_SESSION['REDIRECT'] = 'on';
	require_once($_SERVER['DOCUMENT_ROOT'] . "/../includes/getLogin.php");
	unset($_SESSION['REDIRECT']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Agent Documentation - Database Selection</title>
		<!-- Add Stylesheets -->
		<link type='text/css' rel='stylesheet' href='/css/jqx.base.css' />
		<link type='text/css' rel='stylesheet' href='/css/jqx.darkblue.css' />
		<link type='text/css' rel='stylesheet' href='/css/stylesheet.css' />

		<!--Retrieve jquery for inclusion-->
		<script type='text/javascript' src='/js/jquery.js'></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcore.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqx-all.js"></script>
<!--
		<script type="text/javascript" src="/js/jqwidgets/jqxbuttons.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxnotification.js"></script>
	    <script type="text/javascript" src="/js/jqwidgets/jqxscrollbar.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxlistbox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxcombobox.js"></script>
		<script type="text/javascript" src="/js/jqwidgets/jqxmenu.js"></script>
-->
		<script type='text/javascript' src='/js/jquery.form.js'></script>
		<script type='text/javascript' src='/js/jquery.cookie.js'></script>
		<script type='text/javascript' src='js/javascript.js'></script>
	</head>
	<body>
	<div id="messageNotification">
		<div id="notificationContent"></div>
	</div>
<?php
	if ($databaseClass != "") {
		include $_SERVER['DOCUMENT_ROOT'] . "/../includes/getJQXMenu.php";
	}
?>		
		<div id='divDBSelector' align='center'>
			<form name='frmDBSelector' id='frmDBSelector'>		
				<table align='center' border=0>
					<tr>
						<td align='center'><b>SELECT A DATABASE</b></td>
					</tr>
					<tr>
						<td align='center'>
							<select class='inline' name='databaseClass' id='databaseClass'>
								<option value='localhost.database.class.php'>LOCALHOST</option>
								<option value='localhost2.database.class.php'>LOCALHOST2</option>
								<option value='tsc.database.class.php'>TSC</option>
								<option value='rnoc.database.class.php'>RETAIL NOC</option>
								<option value='hhnoc.database.class.php'>HH NOC</option>
								<option value='scnoc.database.class.php'>SC NOC</option>
								<option value='srfnoc.database.class.php'>SRF NOC</option>
							</select>
							<input class='inline' type='submit' name='btnSubmit' id='btnSubmit' value='SELECT DATABASE'>
						</td>
					</tr>
				</table>
			</form>
		</div>		
	</body>
</html>