$(document).ready(function() {
/********************************************************************************************************************/

	disableBackspace();
	addElements();
	updateLocationCookie();
	loadSelectors();
});

// ********** FUNCTIONS FROM document.ready() **********

function disableBackspace() {
	// Disable back button if not in an input box to avoid accidently changing URL's
	$(document).off('keydown.backspace').on('keydown.backspace', function (event) {
		if (event.which === 8 && !$(event.target).is("input:not([readonly]), textarea:not([readonly]), div")) {
			event.preventDefault();
		}
	});
}

function updateLocationCookie() {
	// Set a cookie for the URL of the tool that you are using
	$.cookie('lastTool',$(location).attr('pathname'), { expires: 1024, path: '/' });
}

function addElements() {
	addLoader();
	addMenu();
	addConfirmationWindow();
	addAutoCompleteToggleButton();
	addIncludeInactiveToggleButton();
}

//********** FUNCTIONS FROM addElements **********

function addLoader() {
	$('div#formLoad').jqxLoader({
		width: 100,
		height: 60,
		imagePosition: 'top',
		isModal: true,
		theme: 'darkblue'
	});
}

function addMenu() {
	$('div#menu').jqxMenu({
		height: 30,
		showTopLevelArrows: true,
		theme: 'darkblue'
	});
}

function addConfirmationWindow() {
	$('#confirmationWindow').jqxWindow({
		autoOpen: false,
		isModal: true,
		showCloseButton: false,
		resizable: false,
		draggable: false,
		keyboardNavigation: false,
		okButton: $('#yes'),
		position: 'center, center',
		cancelButton: $('#no'),
		initContent: function () {
			$('#yes').jqxButton({
				width: '65px',
				template: 'danger'
			});
			$('#no').jqxButton({
				width: '65px',
				template: 'success'
			});
			$('#no').focus();
		},
		width: 400,
		height: 200,
		theme: 'darkblue'
	});

	// Confirmation Dialog Results
	$('#confirmationWindow').on('close', function (event) {
		if (event.args.dialogResult.OK) {
			// Set SelAgentId based on autoComplete value
			$('#selAgentId').jqxComboBox({disabled: false, autoComplete: false, selectedIndex: -1});
			$('#selAgentId').jqxComboBox({autoComplete: autoComplete});
			// Set selSupervisorId based on autoComplete value
			$('#selSupervisorId').jqxComboBox({disabled: false, autoComplete: false, selectedIndex: -1});
			$('#selSupervisorId').jqxComboBox({autoComplete: autoComplete});
			// Set selDocumentationPeriod based on autoComplete value
			$('#selDocumentationPeriod').jqxComboBox({disabled: false, autoComplete: false, selectedIndex: -1});
			$('#selDocumentationPeriod').jqxComboBox({autoComplete: autoComplete});
			$('#menu').jqxMenu({disabled: false});
			$('#autoComplete').jqxToggleButton({disabled: false});
			$('#includeInactive').jqxToggleButton({disabled: false});
			// Update with Action to perform at cancel *************************************
			$('#divAgentAdminBody').hide();
			messageNotification('ADD/EDIT CANCELLED','error', 5000)
		}
	});
}

function addAutoCompleteToggleButton() {
	// Read the setting for your autoComplete cookie and use it
	autoComplete = $.cookie('autoComplete');

	if (autoComplete == 'true') {
		autoComplete = true;
	} else {
		autoComplete = false;
	}

	// Create autoComplete ToggleButton
	$('#autoComplete').jqxToggleButton({
		width: 250,
		height: 25,
		toggled: autoComplete,
		theme: 'darkblue'
	});
	// Set autoComplete Button text based on autoComplete setting 
	if (autoComplete == true) {
		autoComplete = true;
		$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
	} else {
		autoComplete = false;
		$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
	}
}

function addIncludeInactiveToggleButton() {
	// Create includeInactive ToggleButton
	$('#includeInactive').jqxToggleButton({
		width: 250,
		height: 25,
		toggled: false,
		theme: 'darkblue'
	});
}

//********** FUNCTION FROM addConfirmationWindow **********

function messageNotification(messageText, messageType, messageDuration) {
	$('div#messageNotification').html(messageText, messageType, messageDuration);
	$('div#messageNotification').jqxNotification({
		width: '100%',
		position: "top-right",
		opacity: 0.9,	 
		autoOpen: false,
		closeOnClick: false,
		animationOpenDelay: 800,
		autoClose: true,
		autoCloseDelay: messageDuration,
		template: messageType
	});
	$('#messageNotification').jqxNotification('open');	
}



function loadSelectors(selector = '', supervisorGUID ='') {
	showLoader();
	removeSelectorEvents();
	if (selector == '' || selector == 'selDocumentationPeriod') {
		// Prepare data source for selDocumentationPeriod
		var url = 'dependencies/post-selDocumentationPeriod.php';
		var documentationPeriodSource = {
			datatype: 'json',
			datafields: 
				[
					{name: 'documentationPeriod'},
					{name: 'displayPeriod'},
				],
			url: url,
			type: 'post',
			async: false,
		};
		var documentationPeriodDataAdapter = new $.jqx.dataAdapter(documentationPeriodSource);
		// Create documentationPeriod Combo Box
		$('#selDocumentationPeriod').jqxComboBox({
			source: documentationPeriodDataAdapter,
			valueMember: 'documentationPeriod',
			displayMember: 'displayPeriod',
			width: 150,
			height: 25,
			placeHolder: '--- DOC PERIOD ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: 'darkblue'
		});
		// Select the current month by default
		$('#selDocumentationPeriod').jqxComboBox('selectIndex', 0);
	}
	if (selector == '' || selector == 'selSupervisorId') {
		// Prepare data source for selSupervisorId
		var url = 'dependencies/post-selSupervisorId.php';
		var supervisorSource = {
			datatype: 'json',
			datafields: 
				[
					{name: 'GUID'},
					{name: 'fullName'},
					{name: 'attId'},
					{name: 'displayName'},
					{name: 'supervisorStatus'}
				],
			url: url,
			type: 'post',
			async: false
		};
		var supervisorDataAdapter = new $.jqx.dataAdapter(supervisorSource);
		// Create supervisorId Combo Box
		$('#selSupervisorId').jqxComboBox({
			source: supervisorDataAdapter,
			valueMember: 'GUID',
			displayMember: 'displayName',
			width: 400,
			height: 25,
			placeHolder: '--- SELECT SUPERVISOR ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: 'darkblue'
		});
	}
	
	if (selector == '' || selector == 'selAgentId') {
		// Prepare data source for selAgentId
		var includeInactive = $('#includeInactive').jqxToggleButton('toggled');
		var url = 'dependencies/post-selAgentId.php';
		var agentSource = {
			datatype: 'json',
			datafields: 
				[
					{name: 'GUID'},
					{name: 'fullName'},
					{name: 'attId'},
					{name: 'displayName'}
				],
			url: url,
			data: {
				includeInactive: includeInactive,
				supervisorGUID: supervisorGUID
			},
			type: 'post',
			async: false
		};
		var agentDataAdapter = new $.jqx.dataAdapter(agentSource);
		// Create agentId Combo Box
		$('#selAgentId').jqxComboBox({
			source: agentDataAdapter,
			valueMember: 'GUID',
			displayMember: 'displayName',
			width: 400,
			height: 25,
			placeHolder: '--- SELECT AGENT ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: 'darkblue'
		});
	}
	addSelectorEvents();
	hideLoader();
}

//********** FUNCTION FROM loadSelectors **********

function showLoader() {
	$('div#formLoad').jqxLoader('open');
	$('div#formWrapper').hide();
	$('div#divDocumentationHeader').hide();
}

function hideLoader() {
	$('div#formLoad').jqxLoader('close');
	$('div#formWrapper').show();
	$('div#divDocumentationHeader').show();
}

function removeSelectorEvents() {
	$('#selSupervisorId').off('select');
	$('#selAgentId').off('select');
	$('#selDocumentationPeriod').off('select');
	$('#includeInactive').off('click');
	$('#autoComplete').off('click');
}

function addSelectorEvents() {
	// Function to run when autoComplete ToggleButton is clicked
	$('#autoComplete').on('click', function (event) {
		var autoComplete = $('#autoComplete').jqxToggleButton('toggled');
		if (autoComplete == true) {
			autoComplete = true;
			$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
		} else {
			autoComplete = false;
			$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
		}
		// Update the cookie value
		$.cookie('autoComplete', autoComplete, { expires: 1024, path: '/' });
		// Clear Select Box entries
		$('#selAgentId').jqxComboBox('clearSelection');
		$('#selAgentId').jqxComboBox({autoComplete: autoComplete });
		$('#selSupervisorId').jqxComboBox('clearSelection');
		$('#selSupervisorId').jqxComboBox({autoComplete: autoComplete });
		$('#selDocumentationPeriod').jqxComboBox('clearSelection');
		$('#selDocumentationPeriod').jqxComboBox({autoComplete: autoComplete });
		$('#selDocumentationPeriod').jqxComboBox('selectedIndex', 0);
	});

	// Function to run when includeInactive ToggleButton is clicked
	$('#includeInactive').on('click', function(event) {
		$('#formWrapper').hide();
		$('#formLoad').jqxLoader('open');
		// Remove clearSupervisor from display
		$('#clearSupervisor').addClass('invisible');
		if ($('#includeInactive').jqxToggleButton('toggled') == true) {
			// Disable selSupervisorId, inactive agents were included
			$('#selSupervisorId').jqxComboBox({disabled: true});
			$('#selSupervisorId').jqxComboBox('clearSelection');
			$('#includeInactive')[0].value = 'HIDE INACTIVE AGENTS';
		} else {
			$('#selSupervisorId').jqxComboBox({disabled: false});
			$('#selSupervisorId').jqxComboBox('clearSelection');
			$('#includeInactive')[0].value = 'SHOW INACTIVE AGENTS';
		}
		$('#selAgentId').jqxComboBox('clearSelection');
		console.log('running includeInactive');
		showLoader();
		loadSelectors();
		hideLoader();
	});

	// Clear the selSupervisorId when clearSupervisor is clicked
	$('#clearSupervisor').off('click').on('click', function () {
		// Clear selSupervisorId Selection
		$('#selSupervisorId').jqxComboBox('clearSelection');
		// Clear selAgentId selection
		$('#selAgentId').jqxComboBox('clearSelection');
		// Hide the clearSupervisor Button
		$('#clearSupervisor').addClass('invisible');
		showLoader();
		loadSelectors('selAgentId');
		hideLoader();
	});

	// Function to run if selSupervisorId is changed
	$('#selSupervisorId').off('select').on('select', function (event) {
		var args = event.args;
		if (args) {
			loadSelectors('selAgentId', event.args.item.value);
			if (event.args.item.index != -1) {
				$('#clearSupervisor').removeClass('invisible');
			}
		}
	});

	// Function to run if selAgentId or selDocumentationPeriod is changed
	$('#selAgentId, #selDocumentationPeriod').off('select').on('select', function (event) {
		var args = event.args;
		if (args) {
			if (event.args.item.index != -1) {
				if ($('#selDocumentationPeriod').jqxComboBox('selectedIndex') != -1) {
					agentGUID = $('#selAgentId').jqxComboBox('val');
					documentationPeriod = $('#selDocumentationPeriod').jqxComboBox('val');
					getDocumentationHeader(agentGUID, documentationPeriod);
				}
			}
		}
	});
}

//********** FUNCTION FROM addSelectorEvents **********

function getDocumentationHeader(agentGUID, documentationPeriod) {
	showLoader();
	$('divDocumentationHeader').html('');
	url = 'dependencies/post-divDocumentationHeader.php';
	loadDocumentationHeader = $.ajax({
		type: 'POST',
		url: url,
		data: {
			agentGUID: agentGUID,
			documentationPeriod: documentationPeriod
		},
		dataType: 'HTML'
	})
	.done(function(data) {
// Temporary Line 
		$('#divDocumentationHeader').show();
		$('#divDocumentationHeader').html(data);
		addEditableSupervisor();
		addEditableDisciplinaryStep();
		addEditableAgentNotes();
		addDocumentationSummaryTable();
	});
	hideLoader();
	$('divDocumentationHeader').show();
}

function addEditableSupervisor() {
	if ($('#editSupervisor').length >0) {
		$('#editSupervisor').jqxComboBox({
			valueMember: 'GUID',
			displayMember: 'displayName',
			width: 250,
			height: 25,
			placeHolder: '--- SELECT SUPERVISOR ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: 'darkblue'
		});
		supervisorGUID = $('div#supervisorGUID').html();
		supervisorItem = $('#editSupervisor').jqxComboBox('getItemByValue', supervisorGUID);
		$('#editSupervisor').jqxComboBox('selectItem', supervisorItem);
		$('#editSupervisor').on('select', function(event) {
			var args = event.args;
			if (args) {
				var GUID = $('#selAgentId').jqxComboBox('val');
				var value = args.item.value;
				$.ajax({
					type: 'POST',
					url: 'dependencies/post-saveDivDocumentationHeader.php',
					data: {
						GUID: GUID,
						field: 'supervisorGUID',
						value: value
					},
					dataType: 'json',
					success: function(data) {
						messageNotification(data.message, data.type, 2000);
					}
				});
			}
		});
	}	
}

function addEditableDisciplinaryStep() {
	if ($('#editDisciplinaryStep').length >0) {
		$('#editDisciplinaryStep').jqxComboBox({
			valueMember: 'stepName',
			displayMember: 'displayName',
			width: 250,
			height: 25,
			placeHolder: '--- SELECT DISCIPLINARY STEP ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: 'darkblue'
		});
		disciplinaryStep = $('div#disciplinaryStep').html();
		disciplinaryStepItem = $('#editDisciplinaryStep').jqxComboBox('getItemByValue', disciplinaryStep);
		$('#editDisciplinaryStep').jqxComboBox('selectItem', disciplinaryStepItem);
		$('#editDisciplinaryStep').on('select', function(event) {
			var args = event.args;
			if (args) {
				var GUID = $('#selAgentId').jqxComboBox('val');
				var value = args.item.value;
				$.ajax({
					type: 'POST',
					url: 'dependencies/post-saveDivDocumentationHeader.php',
					data: {
						GUID: GUID,
						field: 'disciplinaryStep',
						value: value
					},
					dataType: 'json',
					success: function(data) {
						messageNotification(data.message, data.type, 2000);
					}
				});
			}
		});
	}	
}

function addEditableAgentNotes() {
	if ($('#editAgentNotes').length >0) {
		$('#editAgentNotes').jqxEditor({
			tools: 'bold italic underline | format font size | color background | left center right | outdent indent | ul ol | image | link',
			theme: 'darkblue'
		});
		$('#editAgentNotes').on('change', function (event) {
			var GUID = $('#selAgentId').jqxComboBox('val');
			var value = $('#editAgentNotes').html();
			$.ajax({
				type: 'POST',
				url: 'dependencies/post-saveDivDocumentationHeader.php',
				data: {
					GUID: GUID,
					field: 'agentNotes',
					value: value
				},
				dataType: 'json',
				success: function(data) {
					messageNotification(data.message, data.type, 2000);
				}
			});
		});
	}
}
		
function addDocumentationSummaryTable() {
	if ($('#tblDocumentationSummary').length > 0) {
		$('#tblDocumentationSummary').jqxDataTable({
			altRows: true,
			sortable: true,
			selectionMode: 'singleRow',
			columnsResize: true,
			enableHover: false,
			columns: [
				{text: '', dataField: 'ACTION1', width: 30, cellClassName: 'noRightBorder view', cellsAlign: 'center', resizable: false, sortable: false,
					cellsRenderer: function (row, column, value, rowData) {
						switch (value) {
							case 'VIEW':
								return "<img src='/images/viewRecord.png' />";
								break;
							case 'ADD':
								return "<img src='/images/addRecord.png' />";
								break;
							default:
								return "";
						}
					}
				},
				{text: '', dataField: 'ACTION2', width: 30, cellClassName: 'noSideBorder edit', cellsAlign: 'center', resizable: false, sortable: false,
					cellsRenderer: function (row, column, value, rowData) {
						switch (value) {
							case 'EDIT':
								return "<img src='/images/editRecord.png' />";
								break;
							default:
								return "";
								break;
						}
					}
				},
				{text: '', dataField: 'ACTION3', width: 30, cellClassName: 'noLeftBorder delete', cellsAlign: 'center', resizable: false, sortable: false,
					cellsRenderer: function (row, column, value, rowData) {
						switch (value) {
							case 'DELETE':
								return "<img src='/images/deleteRecord.png' />";
								break;
							default:
								return "";
								break;
						}
					}
				},
				{text: 'GUID', dataField: 'GUID', width: 300, align: 'center'},
				{text: 'DATE', dataField: 'DATE', width: 100, align: 'center', cellsAlign: 'center'},
				{text: 'CREATED BY', dataField: 'CREATED BY', width: 150, align: 'center'},
				{text: 'TYPE', datafield: 'TYPE', width: 250, align: 'center'},
				{text: 'REFERENCE', dataField: 'REFERENCE', width: 500, align: 'center'}
			],
			theme: 'darkblue'
		});
		$('#tblDocumentationSummary').jqxDataTable('hideColumn','GUID');

		// Setup events when a row is clicked
		$('#tblDocumentationSummary').on('rowClick', function(event){
			guid = event.args.row.GUID;
			cellClicked = event.args.dataField;
			switch (cellClicked) {
				case 'ACTION1':
					cellValue = event.args.row.ACTION1;
					switch (cellValue) {
						case 'VIEW':
							console.log('VIEWING');
							break;
						case 'ADD':
							console.log('ADDING');
							break;
					}
					break;
				case 'ACTION2':
					cellValue = event.args.row.ACTION2;
					switch (cellValue) {
						case 'EDIT':
							console.log('EDITING');
							break;
					}
					break;
				case 'ACTION3':
					cellValue = event.args.row.ACTION3;
					switch (cellValue) {
						case 'DELETE':
							console.log('DELETING');
							break;
					}
					break;
			}
		});
	}
}