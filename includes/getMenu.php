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
	 
	$menu = "";
	if (isset($userInfo['userPermissions']['tools'])) {
		if (isset($userInfo['userPermissions']['tools']['TOOL_AGENT_DOCUMENTATION'])) {
			$menu .= " [&nbsp<a style='text-decoration: none;' href='../tool_agent_documentation/'>Agent&nbsp;Documentation</a>&nbsp;]\n"; 
		}
		if (isset($userInfo['userPermissions']['tools']['REPORTING_TRANSCRIPT_BY_AGENT'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../reporting_transcript_by_agent/'>Transcript&nbsp;By&nbsp;Agent</a>&nbsp;]\n";
		}
		if (isset($userInfo['userPermissions']['tools']['REPORTING TRANSCRIPT BY TEAM'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../reporting_transcript_by_team/'>Transcript&nbsp;By&nbsp;Historical&nbsp;Team</a>&nbsp;]\n";
		}
		if (isset($userInfo['userPermissions']['tools']['REPORTING_DOC_COUNT_BY_TEAM'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../reporting_count_by_team/'>Documentation&nbsp;Count&nbsp;By&nbsp;Historical&nbsp;Team</a>&nbsp;]\n";
		}
		if (isset($userInfo['userPermissions']['tools']['ADMINISTRATION_AGENT'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../administration_agent/'>Add/Edit&nbsp;Agents</a>&nbsp;]\n";
		} 
		if (isset($userInfo['userPermissions']['tools']['ADMINISTRATION_SUPERVISOR'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../administration_supervisor/'>Add/Edit&nbsp;Supervisors</a>&nbsp;]\n";
		}
		if (isset($userInfo['userPermissions']['tools']['ADMINISTRATION_EXTERNAL_USER'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../administration_external_user/'>Add/Edit&nbsp;Others</a>&nbsp;]\n";
		}
		if (isset($userInfo['userPermissions']['tools']['ADMINISTRATION_TEAM'])) {
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../administration_team/'>Historical&nbsp;Team&nbsp;Roster</a>&nbsp;]\n";
		}
//		
//		$edit_permissions = "";
//		foreach ($user_permissions as $value) {
//			if ($value == "F" || $value == "A" || $value=="S") {
//				$edit_permissions = "Y";
//			}
//		}	
//		if ($edit_permissions == "Y") {
//			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../administration_edit_permissions/'>Edit&nbsp;Permissions</a>&nbsp;]\n";
//		}
		if (isset($userInfo['userPermissions']['tools']['QUALITY_REVIEW'])) {
			$menu .= "<br /> [&nbsp;<a style='text-decoration: none;' href='../quality_review/'>Add/Edit Quality Eval</a>&nbsp;]\n";
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../quality_review_summary/'>Evaluation Summary</a>&nbsp;]\n";	
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../quality_analytics/'>Evaluation Analytics</a>&nbsp;]\n";	
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../\quality_ticketSystemsAdmin/'>Define Form Types</a>&nbsp;]\n";	
			$menu .= " [&nbsp;<a style='text-decoration: none;' href='../quality_ticketSystemQuestionsAdmin/'>Update Form Questions</a>&nbsp;]\n";			
		}
		if ($databaseName == "") {
			$databaseName = "Not Set";
		}
	}
	echo "<div align='center' class='noPrint'><div style='float: right; font-size: 70%;'>Database Instance: [ <b><span id='dbname' name='dbname'>$databaseName</span></b> ] <a href='../db_selector/'><img src='../images/edit.jpg' border='0' alt='Change Instance'></a></div><br /><div align='center' class='noPrint' style='font-size: 70%;'>$menu</div></div>";
?>