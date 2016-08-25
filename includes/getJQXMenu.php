<?php
/******************************************************************************
This generates the menus displayed throughought the applications based on
what permissions an agent has.   If the database columns are renamed, or if you
add another tool, you wll need to update this file to get it on the menu

Permissions must start with tool_perm (not case sensitive) in the column name
to be considered a tool permission that would give an entry.  

Tool permissions give permissions to a tool overall whereas misc_perm would be
a possible toggle setting that is not a tool but a setting within a tool

The permission names have the tool_perm stripped off them and are converted to
upper caae, so should be evaluated as such here
*******************************************************************************/

	// Create Menu Base
	$menu = "<div id='menu'>";
	$menu .= "	<ul>";
	$menu .= "		<li><a href='/db_selector'/'>DATABASE: [ <b>$databaseName </b> ]</a></li>";

	
	// Check for any Permissions that fall under the ADMINISTRATION menu
	if (isset($userInfo['userPermissions']['tools']['ADMINAGENT']) || isset($userInfo['userPermissions']['tools']['ADMINSUPERVISOR']) || isset($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'])) {
		
		// One or more Administrative permissions were found, add an "ADMINISTION" menu
		$menu .= "		<li>ADMINISTRATION";
		$menu .= "			<ul>";
			
		// Check for USER Administrative permissinos
		if (isset($userInfo['userPermissions']['tools']['ADMINAGENT']) || isset($userInfo['userPermissions']['tools']['ADMINSUPERVISOR']) || isset($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'])) {

			// One or more USER administrative permissions were found, add a "USERS" sub menu
			$menu .= "				<li>USERS";
			$menu .= "					<ul>";
			
			// Check for Agent Administration Permissions
			if (isset($userInfo['userPermissions']['tools']['ADMINAGENT'])) {

				// ADMINAGENT permission was found, add correct AGENT item to submenu
				switch ($userInfo['userPermissions']['tools']['ADMINAGENT']) {
					case "ADD":
						$menu .= "						<li><a href='/adminAgent/'>ADD AGENTS</a></li>";
						break;
					case "EDIT":
						$menu .= "						<li><a href='/adminAgent/'>EDIT/VIEW AGENTS</a></li>";
						break;
					case "VIEW":
						$menu .= "						<li><a href='/adminAgent/'>VIEW AGENTS</a></li>";
						break;
					case "ALL":
						$menu .= "						<li><a href='/adminAgent/'>ADD/EDIT/VIEW AGENTS</a></li>";
						break;
					case "SUPER":
						$menu .= "						<li><a href='/adminAgent/'>ADD/EDIT/VIEW AGENTS</a></li>";
						break;

				}
			}

			// Check for Supervisor Administration Permissions
			if (isset($userInfo['userPermissions']['tools']['ADMINSUPERVISOR'])) {

				// ADMINSUPERVISOR permission was found, add correct SUPERVISOR item to submenu
				switch ($userInfo['userPermissions']['tools']['ADMINSUPERVISOR']) {
					case "ADD":
						$menu .= "						<li><a href='/adminSupervisor/'>ADD SUPERVISORS</a></li>";
						break;
					case "EDIT":
						$menu .= "						<li><a href='/adminSupervisor/'>EDIT/VIEW SUPERVISORS</a></li>";
						break;
					case "VIEW":
						$menu .= "						<li><a href='/adminSupervisor/'>VIEW SUPERVISORS</a></li>";
						break;
					case "ALL":
						$menu .= "						<li><a href='/adminSupervisor/'>ADD/EDIT/VIEW SUPERVISORS</a></li>";
						break;
					case "SUPER":
						$menu .= "						<li><a href='/adminSupervisor/'>ADD/EDIT/VIEW SUPERVISORS</a></li>";
						break;

				}
			}

			// Check for External User Administration Permissions
			if (isset($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER'])) {

				// ADMINEXTERNALUSER permission was found, add correct EXTERNAL USER item to submenu
				switch ($userInfo['userPermissions']['tools']['ADMINEXTERNALUSER']) {
					case "ADD":
						$menu .= "						<li><a href='/adminExternalUser/'>ADD EXTERNAL USERS</a></li>";
						break;
					case "EDIT":
						$menu .= "						<li><a href='/adminExternalUser/'>EDIT/VIEW EXTERNAL USERS</a></li>";
						break;
					case "VIEW":
						$menu .= "						<li><a href='/adminExternalUser/'>VIEW EXTERNAL USERS</a></li>";
						break;
					case "ALL":
						$menu .= "						<li><a href='/adminExternalUser/'>ADD/EDIT/VIEW EXTERNAL USERS</a></li>";
						break;
					case "SUPER":
						$menu .= "						<li><a href='/adminExternalUser/'>ADD/EDIT/VIEW EXTERNAL USERS</a></li>";
						break;

				}
			}
			
			// Close USERS Submenu
			$menu .= "					</ul>";
			$menu .= "				</li>";
		}

		// Close ADMINISTATION Menu
		$menu .= "			</ul>";
		$menu .= "		</li>";
	}
	
	// Check for any Main Tools Permissions
	if (isset($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'])) {	

		// One or more Main Tools were found, add an "TOOLS" menu
		$menu .= "		<li>TOOLS";
		$menu .= "			<ul>";

		// Check for Agent Documentation Tool Permissions
		if (isset($userInfo['userPermissions']['tools']['AGENTDOCUMENTATION'])) {

			// AGENT_DOCUMENTATION permission was found, add correct AGENT DOCUMENTATION item
			$menu .= "				<li><a href='/agentDocumentation/'>AGENT DOCUMENTATION</a></li>";
			$menu .= "			</ul>";
			$menu .= "		</li>";
		}
	}

	// Close menu
	$menu .= "	</ul>";
	$menu .= "</div>";
	
	// Add the generated menu
	echo $menu;
?>