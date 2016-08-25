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

	// Confirmation Dialog Results
	$('#confirmationWindow').on('close', function (event) {
		if (event.args.dialogResult.OK) {
			$('#selExternalUserId').jqxComboBox({disabled: false, autoComplete: false, selectedIndex: -1});
			$('#selExternalUserId').jqxComboBox({autoComplete: autoComplete});
			$('#menu').jqxMenu({disabled: false});
			$('#btnAddExternalUser').jqxButton({disabled: false});
			$('#autoComplete').jqxToggleButton({disabled: false});
			$('#divExternalUserAdminBody').hide();
			messageNotification("ADD/EDIT CANCELLED","error", 5000)
		}
	});
	
	// Create menu
	$("#menu").jqxMenu({
		height: 30,
		showTopLevelArrows: true,
		theme: 'darkblue'
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
		autoComplete = true;
		$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
	} else {
		autoComplete = false;
		$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
	}

	// Update autoComplete when ToggleButton is clicked
	$('#autoComplete').on('click', function (event) {
		var autoComplete = $('#autoComplete').jqxToggleButton('toggled');
		if (autoComplete == true) {
			autoComplete == true;
			$('#autoComplete')[0].value = 'SHOW ONLY MATCHES'
		} else {
			autoComplete == false;
			$('#autoComplete')[0].value = 'SHOW CLOSEST MATCH'			
		}
		$.cookie('autoComplete', autoComplete, { expires: 1024, path: '/' });
		$("#selExternalUserId").jqxComboBox('clearSelection');
		$('#divExternalUserAdminBody').hide();
		$("#selExternalUserId").jqxComboBox({autoComplete: autoComplete });
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

	// Prepare data source for selExternalUserId Combo Box
	var url = "dependencies/post-selExternalUserId.php";
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
			// Function to run when selExternalUserId is loaded
			loadComplete: selExternalUserIdLoaded
	});
	// Create selExternalUserId Combo Box
	$("#selExternalUserId").jqxComboBox({
		source: dataAdapter,
		valueMember: "GUID",
		displayMember: "displayName",
		width: 400,
		height: 25,
		placeHolder: '--- SELECT EXTERNAL USER ---',
		searchMode: 'containsignorecase',
		autoComplete: autoComplete,
		selectionMode: 'dropDownList',
		enableBrowserBoundsDetection: true,
		theme: "darkblue"
	});	

	// Function to run when the value in the selExternaluserId AutoComplete box is changed (selected)
	$('#selExternalUserId').on('select', function (event) {
		var args = event.args;
		if (args) {
			// index represents the item's index.                          
			var index = args.index;
			var item = args.item;
			// get item's index.  A value of -1 means nothing is selected currently
			if (index != -1) {
				var GUID = item.value;
				showExternalUserDataForm(GUID)
			}
		}
	});

	// Create btnAddExternalUser Button
	$("input#btnAddExternalUser").jqxButton({
		roundedCorners: 'all',
		width: 180,
		height: 45,
		theme: 'darkblue'
	});
	// Function to run when btnAddExternalUser is clicked
	$("input#btnAddExternalUser").off('click').on('click', function () {
		addNewExternalUser();
	});

	// Create externalUserStatus Button Group
	$('#buttonExternalUserStatus').jqxButtonGroup({
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

	// Create Save Button
	$("#btnSave").jqxButton({	
		roundedCorners: 'all',
		value: "SAVE",
		width: 120,
		height: 45,
		template: 'success'
	});
	$("#btnSave").off('click').on('click', function() {
		$('#frmExternalUserAdministration').submit();
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
		}
	})

	// Hide Form on initial load
	$('div#divExternalUserAdminBody').hide();


	var options = { 
		url:  'dependencies/post-saveExternalUserData.php', 
		type: 'post',
		dataType: 'json',
		beforeSerialize: correctData, // Run this before validating the entries to capitalize/lowercase fields and trim values
		beforeSubmit:  validateData,  // Run this before Submit
		success:       CompletePost  // Run This after Submit					
	};
	$('#frmExternalUserAdministration').ajaxForm(options);
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

// Setup for adding a new externalUser record

function addNewExternalUser() {
	disableEvents();
	// Get current value of autoComplete, if enabled, turn it off while we reset the autoComplete box as it caused issues with it selecting the first item if enable
	var autoComplete = $('#selExternalUserId').jqxComboBox('autoComplete');
	$('#selExternalUserId').jqxComboBox({autoComplete: false, selectedIndex: -1, placeHolder: "--- NEW EXTERNAL USER --- ", disabled: true});
	$('#selExternalUserId').jqxComboBox({autoComplete: autoComplete, disabled: false});
	// Reset the form to quickly clear data
	$('#frmExternalUserAdministration').resetForm();
	// Show the externalUser data form, and update it if necessary for correct buttons
	showExternalUserDataForm('');
	// Disable all widget actions until form is loaded to avoid autolocking
	$("#buttonExternalUserStatus").off('buttonclick');
	// Set buttons to defaults
	$("#buttonExternalUserStatus").jqxButtonGroup('setSelection', 0);
	$("#externalUserStatus").val("ACTIVE")
	// Setup Bindings to lock the record on updates
	$('.changekey').off('keyup').on('keyup', function() { lockRecord(); });
	$('.changeclick').off('change').on('change', function() { lockRecord(); });
	// Update the HTML for Record
	$('table.bordered tbody > tr > td.Record').html("NEW EXTERNAL USER");
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
	$("#buttonExternalUserStatus").off('buttonclick');
}

/********************************************************************************************************************/

// turns on events after filling the page with necessary information

function setupEvents() {
	// Action when widget ExternalUserStatus is clicked
	$("#buttonExternalUserStatus").off('buttonclick').on('buttonclick', function (event) {
		clickedButton = event.args.button;
		id = clickedButton[0].id;
		$("#externalUserStatus").val(id);
		lockRecord();
	});
}

/********************************************************************************************************************/

// selExternalUserID is loaded, you may now display the form

function selExternalUserIdLoaded() {
	// show form and hide loader
	$("div#formWrapper").show();
	$("div#formLoad").jqxLoader('close');
}

/********************************************************************************************************************/

// Show the subform (the externalUser data form) when an item is selected from above or the new entry button is clicked

function showExternalUserDataForm(GUID) {
	$("div#formLoad").jqxLoader('open');
	// Remove any error borders and Show divExternalUserAdminBody
	$('.errorBorder').removeClass('errorBorder');
	$('#btnAddExternalUser').removeAttr('disabled','disabled');
	$('div#divExternalUserAdminBody').show();
	$('#btnSave').hide();
	$('#btnCancel').hide();
	retrieveData(GUID);
}

/********************************************************************************************************************/

// Retrieve the ExternalUser Data then place it in the appropriate fields

function retrieveData(GUID) {
	// Retrieve Data from Query
	$.ajax({
		dataType: "json",
		type: "post",
		url: "dependencies/post-retrieveExternalUserData.php",
		data: {
			'GUID': GUID
		},
		success: function(externalUserInfo) { parseData(externalUserInfo, GUID) }
	});
	$('.changekey').off('keyup').on('keyup', function() { lockRecord('key'); });
	$('.changeclick').off('change').on('change', function() { lockRecord('click'); });
}

/********************************************************************************************************************/

// Lock record, by disabling select box add new externalUser button, and adding cancel and save buttons

function lockRecord(type) {
	// Record has begun editing, disable selctions until you save or cancel changes.
	$("#selExternalUserId").jqxComboBox({ disabled: true }); 
	$('#menu').jqxMenu({disabled: true});
	$('#btnAddExternalUser').jqxButton({disabled: true});
	$('#autoComplete').jqxToggleButton({disabled: true});
	$('#btnCancel').show();
	$('#btnSave').show();
	$('.changekey').off('keyup');
	$('.changeclick').off('change');
}

/********************************************************************************************************************/

// Function to Parse the externalUser data to fields

function parseData(externalUserData) {
	if (externalUserData.GUID != "") {
		disableEvents();
		$('table.bordered tbody > tr > td.Record').html(externalUserData.firstName + " " + externalUserData.lastName);
		$('#GUID').val(externalUserData.GUID);
		$('#attId').val(externalUserData.attId);
		$('#loginId').val(externalUserData.loginId);
		$('#lastName').val(externalUserData.lastName);
		$('#firstName').val(externalUserData.firstName);
		$('#emailAddress').val(externalUserData.emailAddress);
	
		switch (externalUserData.externalUserStatus) {
			case "ACTIVE":
				$("#buttonExternalUserStatus").jqxButtonGroup('setSelection', 0);
				break;
			case "INACTIVE":
				$("#buttonExternalUserStatus").jqxButtonGroup('setSelection', 1);
				break;
			default:
				$("#buttonExternalUserStatus").jqxButtonGroup('setSelection', 0);
				break;
		}
		$('#externalUserStatus').val(externalUserData.externalUserStatus);

		if (externalUserData.GUID == externalUserData.userGUID || (externalUserData.ADMINEXTERNALUSER != "EDIT" && externalUserData.ADMINEXTERNALUSER != "ALL" && externalUserData.ADMINEXTERNALUSER != "SUPER")) {
			// You are editing your own record, disable entries (still checked on save to avoid bypassing via turning on fields again, and display a notification window indicating why you cannot edit).
			$('input#GUID').attr('disabled', 'disabled');
			$('input#firstName').attr('disabled','disabled');
			$('input#lastName').attr('disabled','disabled');
			$('input#attId').attr('disabled','disabled');
			$('input#loginId').attr('disabled','disabled');
			$('input#emailAddress').attr('disabled','disabled');

			// Disable externalUserStatus
			$('#buttonExternalUserStatus').jqxButtonGroup({ disabled: true });
			if ((externalUserData.ADMINEXTERNALUSER == "EDIT" || externalUserData.ADMINEXTERNALUSER == "ALL" || externalUserData.ADMINEXTERNALUSER != "SUPER") && externalUserData.GUID == externalUserData.userGUID) {
				messageNotification("EDITING DISABLED FOR YOUR OWN RECORD","warning",5000);
			}
		} else {
			$('input#GUID').removeAttr('disabled');
			$('input#firstName').removeAttr('disabled');
			$('input#lastName').removeAttr('disabled');
			$('input#attId').removeAttr('disabled');
			$('input#loginId').removeAttr('disabled');
			$('input#emailAddress').removeAttr('disabled');
			if ($('#buttonExternalUserStatus').jqxButtonGroup('disabled')) $('#buttonExternalUserStatus').jqxButtonGroup({ disabled: false });
		}

		// If your editing a record, and are not super access, disable the attId and loginId fields
		if (externalUserData.GUID != "" && externalUserData.ADMINEXTERNALUSER != "SUPER") {
			$('input#attId').attr('disabled', 'disabled');
			$('input#loginId').attr('disabled', 'disabled');
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
		$('#selExternalUserId').jqxComboBox({ disabled: false });

		$('.changekey').off('keyup').on('keyup', function() { lockRecord(); });
		$('.changeclick').off('change').on('change', function() { lockRecord(); });
		$('#menu').jqxMenu({disabled: false});
		$('#btnAddExternalUser').jqxButton({disabled: false});
		$('#autoComplete').jqxToggleButton({disabled: false});
		$('#divExternalUserAdminBody').hide();
		// Prepare data source for selExternalUserId Combo Box
		var url = "dependencies/post-selExternalUserId.php";
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
			// Function to run when selExternalUserId is loaded
			loadComplete: selExternalUserIdLoaded
		});
		// Create selExternalUserId Combo Box
		$("#selExternalUserId").jqxComboBox({
			source: dataAdapter,
			valueMember: "GUID",
			displayMember: "displayName",
			width: 400,
			height: 25,
			placeHolder: '--- SELECT EXTERNAL USER ---',
			searchMode: 'containsignorecase',
			autoComplete: autoComplete,
			selectionMode: 'dropDownList',
			enableBrowserBoundsDetection: true,
			theme: "darkblue"
		});	
	}
}