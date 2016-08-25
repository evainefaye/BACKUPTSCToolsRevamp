			<div id='divDocumentationSelections'>
				<form name='frmAgentDocumentationSelections' id='frmAgentDocumentationSelections'>
					<table class='bordered centered filled spaced'>
						<tr>
							<td class='labelAbove'>DOCUMENTATION<BR />PERIOD</td>
						</tr>
						<tr>
							<td align='center'>
								<div id='selDocumentationPeriod'></div>
							</td>
						</tr>					
					</table>
					<div class='hide' id='selSupervisorId'></div>
					<div class='hide' id='selAgentId'></div>
					<div class='hide' id='autoComplete'></div>
					<div class='hide' id='includeInactive'></div>
					<div class='hide' id='GUID'>
<?php
	echo $_SESSION['userInfo']['GUID'] 
?>
</div>
				</form>
			</div>