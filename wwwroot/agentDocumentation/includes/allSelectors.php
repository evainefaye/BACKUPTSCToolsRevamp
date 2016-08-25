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