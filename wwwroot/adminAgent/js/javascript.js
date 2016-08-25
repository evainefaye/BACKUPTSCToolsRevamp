$(document).ready(function() {
/********************************************************************************************************************/
// Automatically run functions when initial document is ready.

	// Set a cookie for the URL of the tool that you are using
	$.cookie('lastTool',$(location).attr('pathname'), { expires: 1024, path: '/' });

	// Create Confirmation Window
	$("#confirmationWindow").jqxWindow({
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

	// Create menu
	$("#menu").jqxMenu({
		height: 30,
		showTopLevelArrows: true,
		theme: 'darkblue'
	});

	// Confirmation Dialog Results
	$('#confirmationWindow').on('close', function (event) {
		if (event.args.dialogResult.OK) {
			$('#selAgentId').jqxComboBox({disabled: false, autoComplete: false, selectedIndex: -1});
			$('#selAgentId').jqxComboBox({autoComplete: autoComplete});	
			$('#menu').jqxMenu({disabled: false});
			$('#btnAddAgent').jqxButton({disabled: false});
			$('#autoComplete').jqxToggleButton({disabled: false});
			$('#divAgentAdminBody').hide();
			messageNotification("ADD/EDIT CANCELLED","error", 5000)
		}
	});
	
	// Disable back button if not in an input box to avoid accidently changing URL's
	$(document).off("keydown").on("keydown", function (event) {
		if (event.which === 8 && !$(event.target).is("input:not([readonly]), textarea:not([readonly])")) {
			event.preventDefault();
		}
	});

	// Read the setting for your autoComplete cookie and use it
	autoComplete = $.cookie('autoComplete');

	if (autoComplete == 'true') {
		autoComplete = true;
	} else {
		autoComplete = false;
	}

	// Create autoComplete ToggleButton
	$("#autoComplete").jqxToggleButton({
		width: 250,
		height: 45,
		toggled: autoComplete,
		theme: 'darkblue'
	});
	// Set autoComplete Button text based on autoComplete setting 
	if (autoComplete == true) {
		autoComplete == true;
		$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
	} else {
		autoComplete == false;
		$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
	}

	// Update autoComplete when ToggleButton is clicked
	$('#autoComplete').on('click', function (event) {
		var autoComplete = $('#autoComplete').jqxToggleButton('toggled');
		if (autoComplete == true) {
			autoComplete = true;
			$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
		} else {
			autoComplete = false;
			$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
		}
		$.cookie('autoComplete', autoComplete, { expires: 1024, path: '/' });
		$("#selAgentId").jqxComboBox('clearSelection');
		$('#divAgentAdminBody').hide();
		$("#selAgentId").jqxComboBox({autoComplete: autoComplete });
	});

	// Hide form and display loader until its loaded
	$("div#formWrapper").hide();
	$("div#formLoad").jqxLoader({
		width: 100,
		height: 60,
		imagePosition: 'top',
		isModal: true,
		theme: 'darkblue'
	});
	$("div#formLoad").jqxLoader('open');

	// Prepare data source for selAgentId Combo Box
	var url = "dependencies/post-selAgentId.php";
	var source = {
		datatype: "json",
		datafields: 
			[
				{ name: 'GUID' },
				{ name: 'fullName' },
				{ name: 'attId' },
				{ name: 'displayName' }
			],
		url: url,
		type: 'post',
		async: false
	};
	var dataAdapter = new $.jqx.dataAdapter(source, {
			// Function to run when selAgentId is loaded
			loadComplete: selAgentIdLoaded
	});
	// Create selAgentId Combo Box
	$("#selAgentId").jqxComboBox({
		source: dataAdapter,
		valueMember: "GUID",
		displayMember: "displayName",
		width: 400,
		height: 25,
		placeHolder: '--- SELECT AGENT ---',
		searchMode: 'containsignorecase',
		autoComplete: autoComplete,
		selectionMode: 'dropDownList',
		enableBrowserBoundsDetection: true,
		theme: "darkblue"
	});	

	// Function to run when the value in the selSupervisorId AutoComplete box is changed (selected)
	$('#selAgentId').on('select', function (event) {
		var args = event.args;
		if (args) {
			// index represents the item's index.                          
			var index = args.index;
			var item = args.item;
			// get item's index.  A value of -1 means nothing is selected currently
			if (index != -1) {
				var GUID = item.value;
				showAgentDataForm(GUID)
			}
		}
	});

	// Create btnAddAgent Button
	$("input#btnAddAgent").jqxButton({
		roundedCorners: 'all',
		width: 180,
		height: 45,
		theme: 'darkblue'
	});
	// Function to run when btnAddAgent is clicked
	$("input#btnAddAgent").off('click').on('click', function () {
		addNewAgent();
	});

	// Create agentType Button Group
	$('#buttonAgentType').jqxButtonGroup({
		mode: 'radio',
		theme: 'darkblue'
	});

	// Create agentGender Button Group
	$('#buttonAgentGender').jqxButtonGroup({
		mode: 'radio',
		theme: 'darkblue'
	});

	// Create agentStatus Button Group
	$('#buttonAgentStatus').jqxButtonGroup({
		mode: 'radio',
		theme: 'darkblue'
	});

	// Create tooltips based on a class of tooltip with the value of tooltipText='text to display'
	$('.tooltipFocus').each(function() {
		tooltipText = $(this).attr('tooltipText');
		if (typeof (tooltipText) != 'undefined') {
			$(this).jqxTooltip({
				content: tooltipText,
				position: 'top',
				showArrow: false,
				autoHide: false,
				trigger: 'none',
				closeOnClick: false,
				theme: 'darkblue'
			});
		}
	}); 
	
	// Show/hide tooltips based on focus.
	$('.tooltipFocus').off('focus').on('focus', function (event) {
		$(this).jqxTooltip('open');
	});
	$('.tooltipFocus').off('blur').on('blur', function (event) {
		$(this).jqxTooltip('close');
	});

	// Create tooltips based on a class of tooltipFocus with the value of tooltipText='text to display'
	$('.tooltipHover').each(function() {
		tooltipText = $(this).attr('tooltipText');
		if (typeof (tooltipText) != 'undefined') {
			$(this).jqxTooltip({
				content: tooltipText,
				position: 'bottom',
				showArrow: false,
				autoHide: true,
				trigger: 'hover',
				closeOnClick: false,
				theme: 'darkblue'
			});
		}
	}); 

	// Create Editor for agentNotes
	$('#agentNotes').jqxEditor({
		height: 200,
		tools: 'bold italic underline | font size | color | left center right | outdent indent | ul ol ',
		theme: "darkblue"
	});

	// Create DateTimeInput For calendarHireDate
	$('#calendarHireDate').jqxDateTimeInput({ 
		width: "300px",
		height: "25px",
		max: new Date(),
		dropDownHorizontalAlignment: "right",
		formatString: "MM/dd/yyyy",
		enableBrowserBoundsDetection: true,
		placeHolder: "NOT SET",
		theme: 'darkblue'
	});

	// Create requireViewRestricted Button Group
	$('#buttonRequireViewRestricted').jqxButtonGroup({
		mode: 'radio',
		theme: 'darkblue'
	});

	// Create Save Button
	$("#btnSave").jqxButton({	
		roundedCorners: 'all',
		value: "SAVE",
		width: 120,
		height: 45,
		template: 'success'
	});
	$("#btnSave").off('click').on('click', function() {
		$('#frmAgentAdministration').submit();
	});

	// Create Cancel Button
	$("#btnCancel").jqxButton({
		roundedCorners: 'all',
		width: 120,
		height: 45,
		template: 'warning'
	});
	$("#btnCancel").off('click').on('click', function() {
		$('#confirmationWindow').jqxWindow('open');
	});
	
	// When attId is changed, copy it to loginId and generate email address if those fields are empty
	$('#attId').off('change.update').on('change.update', function() {
		if ($.trim($('#loginId').val()) == "") {
			$('#loginId').val($('#attId').val());
			$('#emailAddress').focus();
		}
		if ($.trim($('#emailAddress').val()) == "") {
			emailAddress = $.trim($('#attId').val()) + "@att.com";
			$('#emailAddress').val(emailAddress);
			if ($.trim($('#loginId').val()) != "") {
				$('#selSupervisorId').focus();
			}
		}
	})

	// Hide Form on initial load
	$('div#divAgentAdminBody').hide();


	var options = { 
		url:  'dependencies/post-saveAgentData.php', 
		type: 'post',
		dataType: 'json',
		beforeSerialize: correctData, // Run this before validating the entries to capitalize/lowercase fields and trim values
		beforeSubmit:  validateData,  // Run this before Submit
		success:       CompletePost  // Run This after Submit					
	};
	$('#frmAgentAdministration').ajaxForm(options);
});

	
/********************************************************************************************************************/

// Displays notification messages

function messageNotification(messageText, messageType, messageDuration) {
	$("#messageNotification").html(messageText, messageType, messageDuration);
	$("#messageNotification").jqxNotification({
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
	$("#messageNotification").jqxNotification("open");	
}

/********************************************************************************************************************/

// Setup for adding a new agent record

function addNewAgent() {
	disableEvents();
	// Get current value of autoComplete, if enabled, turn it off while we reset the autoComplete box as it caused issues with it selecting the first item if enable
	var autoComplete = $('#selAgentId').jqxComboBox('autoComplete');
	$('#selAgentId').jqxComboBox({autoComplete: false, selectedIndex: -1, placeHolder: "--- NEW AGENT --- ", disabled: true});
	$('#selAgentId').jqxComboBox({autoComplete: autoComplete, disabled: false});
	// Reset the form to quickly clear data
	$('#frmAgentAdministration').resetForm();
	// Show the agent data form, and update it if necessary for correct buttons
	showAgentDataForm('');
	// Disable all widget actions until form is loaded to avoid autolocking
	$("#buttonAgentType").off('buttonclick');
	$("#buttonAgentGender").off('buttonclick');
	$("#buttonAgentStatus").off('buttonclick');
	$("#calendarHireDate").off('valueChanged');
	$("#buttonRequireViewRestricted").off('buttonclick');
	$("#selSupervisorId").off('select');
	// Set hireDate to the current date
	currentDate = new Date();
	$('#calendarHireDate').jqxDateTimeInput({value: currentDate});
	$('#hireDate').val(moment(currentDate).format('YYYY-MM-DD'));
	// Prepare data source for supervisorGUID
	var url = "dependencies/post-selSupervisorId.php";
	var supervisorSource = {
		datatype: "json",
		datafields: 
			[
				{ name: 'GUID' },
				{ name: 'fullName' },
				{ name: 'attId' },
				{ name: 'displayName' },
				{ name: 'supervisorStatus' }
			],
		url: url,
		type: 'post',
		async: false
	};
	var supervisorDataAdapter = new $.jqx.dataAdapter(supervisorSource);
	// Create supervisorId Combo Box
	$("#selSupervisorId").jqxComboBox({
		source: supervisorDataAdapter,
		valueMember: "GUID",
		displayMember: "displayName",
		width: 400,
		height: 25,
		placeHolder: '--- SELECT SUPERVISOR ---',
		searchMode: 'containsignorecase',
		autoComplete: false,
		selectionMode: 'dropDownList',
		enableBrowserBoundsDetection: true,
		theme: "darkblue"
	});
	// Set buttons to defaults
	$("#buttonAgentType").jqxButtonGroup('setSelection', 0);
	$("#agentType").val("UA");
	$("#buttonAgentGender").jqxButtonGroup('setSelection', 0);
	$("#agentGender").val("");
	$("#buttonRequireViewRestricted").jqxButtonGroup('setSelection', 0);
	$("#requireViewRestricted").val("DISABLED");
	$("#buttonAgentStatus").jqxButtonGroup('setSelection', 0);
	$("#agentStatus").val("ACTIVE")
	// Clear Agent Notes
	$("#agentNotes").val("");
	// Setup Bindings to lock the record on updates
	$('.changekey').off('keyup').on('keyup', function() { lockRecord(); });
	$('.changeclick').off('change').on('change', function() { lockRecord(); });
	// Update the HTML for Record
	$('table.bordered tbody > tr > td.Record').html("NEW AGENT");
	$('#GUID').val('');
	$('#lastName').focus();
	// Enable widget actions
	setupEvents();
	// Disable loader overlay
	$("div#formLoad").jqxLoader('close');
}
	
/********************************************************************************************************************/

// turns off events for the various widgets to avoid hitting events that causes a record lock

function disableEvents() {
	$("#buttonAgentType").off('buttonclick');
	$("#buttonAgentGender").off('buttonclick');
	$("#buttonAgentStatus").off('buttonclick');
	$("#calendarHireDate").off('valueChanged');
	$("#buttonRequireViewRestricted").off('buttonclick');
	$("#selSupervisorId").off('select');
	$("#agentNotes").off('change');
}

/********************************************************************************************************************/

// turns on events after filling the page with necessary information

function setupEvents() {
	// Action when widget agentType is clicked
	$("#buttonAgentType").off('buttonclick').on('buttonclick', function (event) {
		clickedButton = event.args.button;
		id = clickedButton[0].id;
		$("#agentType").val(id);
		lockRecord();
	});

	// Action when widget AgentGender is clicked
	$("#buttonAgentGender").off('buttonclick').on('buttonclick', function (event) {
		clickedButton = event.args.button;
		id = clickedButton[0].id;
		$("#agentGender").val(id);
		lockRecord();
	});

	// Action when widget AgentStatus is clicked
	$("#buttonAgentStatus").off('buttonclick').on('buttonclick', function (event) {
		clickedButton = event.args.button;
		id = clickedButton[0].id;
		$("#agentStatus").val(id);
		lockRecord();
	});

	// Action when widget Hire Date is changed
	$("#calendarHireDate").off('valueChanged').on('valueChanged', function (event) {
		date = new Date(event.args.date);
		$('#hireDate').val(moment(event.args.date).format('YYYY-MM-DD'));
		lockRecord();
	});

	// Action when widget RequireViewRestricted is changed
	$("#buttonRequireViewRestricted").off("buttonclick").on("buttonclick", function(event) {
		clickedButton = event.args.button;
		id = clickedButton[0].id;
		$("#requireViewRestricted").val(id);
		lockRecord();
	});

	$('#selSupervisorId').off('select').on('select', function (event) {
		var args = event.args;
		if (args) {
			// index represents the item's index.                          
			var index = args.index;
			var item = args.item;
			// get item's index.  A value of -1 means nothing is selected currently
			if (index != -1) {
				var supervisorGUID = item.value;
				$('#supervisorGUID').val(supervisorGUID);
			}
		}
		lockRecord();
	});

	$('#agentNotes').off('change').on('change', function() {
		lockRecord();
	});
}

/********************************************************************************************************************/

// selAgentID is loaded, you may now display the form

function selAgentIdLoaded() {
	// show form and hide loader
	$("div#formWrapper").show();
	$("div#formLoad").jqxLoader('close');
}

/********************************************************************************************************************/

// Show the subform (the agent data form) when an item is selected from above or the new entry button is clicked

function showAgentDataForm(GUID) {
	$("div#formLoad").jqxLoader('open');
	// Remove any error borders and Show divAgentAdminBody
	$('.errorBorder').removeClass('errorBorder');
	$('#btnAddAgent').removeAttr('disabled','disabled');
	$('div#divAgentAdminBody').show();
	$('#btnSave').hide();
	$('#btnCancel').hide();
	retrieveData(GUID);
}

/********************************************************************************************************************/

// Retrieve the Agent Data then place it in the appropriate fields

function retrieveData(GUID) {
	// Retrieve Data from Query
	$.ajax({
		dataType: "json",
		type: "post",
		url: "dependencies/post-retrieveAgentData.php",
		data: {
			'GUID': GUID
		},
		success: function(agentInfo) { parseData(agentInfo, GUID) }
	});
	$('.changekey').off('keyup').on('keyup', function() { lockRecord('key'); });
	$('.changeclick').off('change').on('change', function() { lockRecord('click'); });
}

/********************************************************************************************************************/

// Lock record, by disabling select box add new agent button, and adding cancel and save buttons

function lockRecord(type) {
	// Record has begun editing, disable selctions until you save or cancel changes.
	$('#menu').jqxMenu({disabled: true});
	$("#selAgentId").jqxComboBox({ disabled: true }); 
	$('#btnAddAgent').jqxButton({disabled: true});
	$('#autoComplete').jqxToggleButton({disabled: true});
	$('#btnCancel').show();
	$('#btnSave').show();
	$('.changekey').off('keyup');
	$('.changeclick').off('change');
}

/********************************************************************************************************************/

// Function to Parse the agent data to fields

function parseData(agentData) {
	if (agentData.GUID != "") {
		disableEvents();
		$('table.bordered tbody > tr > td.Record').html(agentData.firstName + " " + agentData.lastName);
		// Prepare data source for supervisorGUID
		var url = "dependencies/post-selSupervisorId.php";
		var supervisorSource = {
			datatype: "json",
			datafields: 
				[
					{ name: 'GUID' },
					{ name: 'fullName' },
					{ name: 'attId' },
					{ name: 'displayName' },
					{ name: 'supervisorStatus' }
				],
			data: {
				supervisorGUID: agentData.supervisorGUID
			},
			url: url,
			type: 'post',
			async: false
		};
		var supervisorDataAdapter = new $.jqx.dataAdapter(supervisorSource);
		// Create supervisorId Combo Box
		$("#selSupervisorId").jqxComboBox({
			source: supervisorDataAdapter,
			valueMember: "GUID",
			displayMember: "displayName",
			width: 400,
			height: 25,
			placeHolder: '--- SELECT SUPERVISOR ---',
			searchMode: 'containsignorecase',
			autoComplete: false,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: "darkblue"
		});
		$('#GUID').val(agentData.GUID);
		$('#attId').val(agentData.attId);
		$('#loginId').val(agentData.loginId);
		$('#lastName').val(agentData.lastName);
		$('#firstName').val(agentData.firstName);
		$('#emailAddress').val(agentData.emailAddress);
		supervisorItem = $('#selSupervisorId').jqxComboBox('getItemByValue', agentData.supervisorGUID);
		$('#selSupervisorId').jqxComboBox('selectItem', supervisorItem);
		$('#supervisorGUID').val(agentData.supervisorGUID);

		switch (agentData.agentType) {
			case "UA":
				$("#buttonAgentType").jqxButtonGroup('setSelection', 0);
				break;
			case "FTE":
				$("#buttonAgentType").jqxButtonGroup('setSelection', 1);
				break;
			default:
				$("#buttonAgentType").jqxButtonGroup('setSelection', 0);
		}
		$('#agentType').val(agentData.agentType);
	
		if (agentData.hireDate == '') {
			currentDate = new Date();
			$('#calendarHireDate').jqxDateTimeInput({value: currentDate});
			$('#hireDate').val(moment(currentDate).format('YYYY-DD-MM'));
		} else {
			$('#calendarHireDate').val(agentData.hireDate);
			$('#hireDate').val(agentData.hireDate);
		}

		switch (agentData.agentGender) {
			case "M":
				$("#buttonAgentGender").jqxButtonGroup('setSelection', 1);
				break;
			case "F":
				$("#buttonAgentGender").jqxButtonGroup('setSelection', 2);
				break;
			default:
				$("#buttonAgentGender").jqxButtonGroup('setSelection', 0);
				break;
		}
		$('#agentGender').val(agentData.agentGender);

		switch (agentData.requireViewRestricted) {
			case "DISABLED":
				$("#buttonRequireViewRestricted").jqxButtonGroup('setSelection', 0);
				break;
			case "ENABLED":
				$("#buttonRequireViewRestricted").jqxButtonGroup('setSelection', 1);
				break;
			default:
				$("#buttonRequireViewRestricted").jqxButtonGroup('setSelection', 0);
		}
		$('#requireViewRestricted').val(agentData.requireViewRestricted);
	
		switch (agentData.agentStatus) {
			case "ACTIVE":
				$("#buttonAgentStatus").jqxButtonGroup('setSelection', 0);
				break;
			case "INACTIVE":
				$("#buttonAgentStatus").jqxButtonGroup('setSelection', 1);
				break;
			default:
				$("#buttonAgentStatus").jqxButtonGroup('setSelection', 0);
				break;
		}
		$('#agentStatus').val(agentData.agentStatus);

		$('#agentNotes').val(agentData.agentNotes);

		if (agentData.GUID == agentData.userGUID || (agentData.ADMINAGENT != "EDIT" && agentData.ADMINAGENT != "ALL" && agentData.ADMINAGENT !="SUPER")) {
			// You are editing your own record, disable entries (still checked on save to avoid bypassing via turning on fields again, and display a notification window indicating why you cannot edit).
			$('input#GUID').attr('disabled', 'disabled');
			$('input#firstName').attr('disabled','disabled');
			$('input#lastName').attr('disabled','disabled');
			$('input#attId').attr('disabled','disabled');
			$('input#loginId').attr('disabled','disabled');
			$('input#emailAddress').attr('disabled','disabled');

			// Disable supervisor combobox
			$('#selSupervisorId').jqxComboBox({ disabled: true });
			// Disable agentType button group
			$('#buttonAgentType').jqxButtonGroup({ disabled: true });
			// Disable hireDate
			$('#calendarHireDate').jqxDateTimeInput({ disabled: true });
			// Disable agentGender button group
			$('#buttonAgentGender').jqxButtonGroup({ disabled: true })
			// Disable requireviewRestricted button group
			$('#buttonRequireViewRestricted').jqxButtonGroup({ disabled: true });
			// Disable agentStatus
			$('#buttonAgentStatus').jqxButtonGroup({ disabled: true });
			// Disable agentNotes
			$('#agentNotes').jqxEditor({ disabled: true });
			if ((agentData.ADMINAGENT == "EDIT" || agentData.ADMINAGENT == "ALL" || agentData.ADMINAGENT == "SUPER") && agentData.GUID == agentData.userGUID) {
				messageNotification("EDITING DISABLED FOR YOUR OWN RECORD","warning",5000);
			}
		} else {
			$('input#GUID').removeAttr('disabled');
			$('input#firstName').removeAttr('disabled');
			$('input#lastName').removeAttr('disabled');
			$('input#attId').removeAttr('disabled');
			$('input#loginId').removeAttr('disabled');
			$('input#emailAddress').removeAttr('disabled');
			if ($('#selSupervisorId').jqxComboBox('disabled')) $('#selSupervisorId').jqxComboBox({ disabled: false });
			if ($('#buttonAgentType').jqxButtonGroup('disabled')) $('#buttonAgentType').jqxButtonGroup({ disabled: false });
			if ($('#calendarHireDate').jqxDateTimeInput('disabled')) $('#calendarHireDate').jqxDateTimeInput({ disabled: false });			
			if ($('#buttonAgentGender').jqxButtonGroup('disabled')) $('#buttonAgentGender').jqxButtonGroup({ disabled: false });
			if ($('#buttonRequireViewRestricted').jqxButtonGroup('disabled')) $('#buttonRequireViewRestricted').jqxButtonGroup({ disabled: false });
			if ($('#buttonAgentStatus').jqxButtonGroup('disabled')) $('#buttonAgentStatus').jqxButtonGroup({ disabled: false });
			if ($('#agentNotes').jqxEditor('disabled')) $('#agentNotes').jqxEditor({ disabled: false }) ;
		}

		// If your editing a record, and are not super access, disable the attId and loginId fields
		if (agentData.GUID != "" && agentData.ADMINAGENT != "SUPER") {
			$('input#attId').attr('disabled', 'disabled');
			$('input#loginId').attr('disabled', 'disabled');
		}

		if (!agentData.enableRestricted) {
			// Disable requireviewRestricted button group
			$('#buttonRequireViewRestricted').jqxButtonGroup({ disabled: true });			
		} 

		setupEvents();
		$("div#formLoad").jqxLoader('close');
	} else {
		$('input#attId').removeAttr('disabled');
		$('input#loginId').removeAttr('disabled');
	}
}

/********************************************************************************************************************/

// Trim form values and correct fields to proper casing

function correctData(jqform, options) {
	lastName = $.trim($('#lastName').val()).toUpperCase();
	$('#lastName').val(lastName);
	firstName = $.trim($('#firstName').val()).toUpperCase();
	$('#firstName').val(firstName);
	attId = $.trim($('#attId').val()).toLowerCase();
	$('#attId').val(attId);
	loginId = $.trim($('#loginId').val()).toLowerCase();
	$('#loginId').val(loginId);
	emailAddress = $.trim($('#emailAddress').val()).toLowerCase();
	$('#emailAddress').val(emailAddress);
}

/********************************************************************************************************************/

// Validate form entries before submitting

function validateData(formData, jqForm, options) { 
	// Start by removing all error notices
	$('.errorBorder').removeClass('errorBorder');
	// Assume there are no errors
	saveRecord = true;
	
	// Make sure lastName is entered
	if ($('#lastName').val() == '') {
		$('.lastName').addClass('errorBorder');
		saveRecord = false;
	}

	// Make sure firstName is entered
	if ($('#firstName').val() == '') {
		$('.firstName').addClass('errorBorder');
		saveRecord = false;
	}

	// Make sure attId is entered
	if ($('#attId').val() == '') {
		$('.attId').addClass('errorBorder');
		saveRecord = false;
	}

	// Make sure loginId is entered
	if ($('#loginId').val() == '') {
		$('.loginId').addClass('errorBorder');
		saveRecord = false;
	}

	// Make sure emailAddress is entered and is a valid email address format
	if ($('#emailAddress').val() == '') {
		$('.emailAddress').addClass('errorBorder');
		saveRecord = false;
	} else {
		var emailRegex = new RegExp(/^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$/i);
		if (!emailRegex.test($('#emailAddress').val())) {
			$('.emailAddress').addClass('errorBorder');
			saveRecord = false;
		}
	}

	// Make sure supervisorGUID is selected
	if ($.trim($('#supervisorGUID').val()) == '') {
		$('.supervisorId').addClass('errorBorder');
		saveRecord = false;
	}
	if (!saveRecord) {
		messageNotification('CORRECT ERRORS ON HIGHLIGHTED FIELDS AND TRY AGAIN', 'error', 5000);
	}
	return saveRecord;
}

/********************************************************************************************************************/

// Called after form is submitted, used to handle success/error notification and resetting form

function CompletePost(responseText, statusText, xhr, form)  {
	// Remove any error borders that existed at start of return from post
	$('.errorBorder').removeClass('errorBorder');
	// Response type of error was generated
	if (responseText.type == "error") {
		// A field was selected, place an errorBorder class on the selected fields
		if (responseText.field != "") {
			$(responseText.field).addClass('errorBorder');
		}
		// Display error message
		messageNotification(responseText.message, responseText.type, 5000);
		return false;
	} else {
		// Display success message
		messageNotification(responseText.message, responseText.type, 5000);
		$('#selAgentId').jqxComboBox({ disabled: false });

		$('.changekey').off('keyup').on('keyup', function() { lockRecord(); });
		$('.changeclick').off('change').on('change', function() { lockRecord(); });
		$('#menu').jqxMenu({disabled: false});
		$('#btnAddAgent').jqxButton({disabled: false});
		$('#autoComplete').jqxToggleButton({disabled: false});
		$('#divAgentAdminBody').hide();
		// Prepare data source for selAgentId Combo Box
		var url = "dependencies/post-selAgentId.php";
		var source = {
			datatype: "json",
			datafields: 
				[
					{ name: 'GUID' },
					{ name: 'fullName' },
					{ name: 'attId' },
					{ name: 'displayName' }
				],
			url: url,
			type: 'post',
			async: false
		};
		var dataAdapter = new $.jqx.dataAdapter(source, {
			// Function to run when selAgentId is loaded
			loadComplete: selAgentIdLoaded
		});
		// Create selAgentId Combo Box
		$("#selAgentId").jqxComboBox({
			source: dataAdapter,
			valueMember: "GUID",
			displayMember: "displayName",
			width: 400,
			height: 25,
			placeHolder: '--- SELECT AGENT ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: "darkblue"
		});	
	}
}