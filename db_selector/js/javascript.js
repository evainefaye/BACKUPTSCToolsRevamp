$(document).ready(function() {

/********************************************************************************************************************/
// Functions to Run Automatically to Populate Select Boxes and Setup Events when Document is ready

	// Form Submission Options
	var database = $.cookie('DATABASE');	
	var options = { 
		url: 'dependencies/post-selectDB.php', // What is called to Save Data
		type: 'post',  // Type of Form Submission
		dataType: 'json',
		success: reset
	};
	$('#frmDBSelector').ajaxForm(options);
	$("input#btnSubmit").jqxButton({
		theme: 'darkblue'
	});
	
	// Turn off Backspace to avoid switching pages
	$(document).on("keydown", function (e) {
		if (e.which === 8 && !$(e.target).is("input:not([readonly]), textarea")) {
			e.preventDefault();
		}
	});
	$("select#databaseClass").val("");
	// Create database combobox
	$("select#databaseClass").jqxComboBox({ 
		placeHolder: '--- SELECT DATABASE ---',
		searchMode: 'containsignorecase',
		autoComplete: false,
		selectionMode: 'dropDownList',
		enableBrowserBoundsDetection: true,
		theme: 'darkblue'
	});

	// Create menu
	$("#menu").jqxMenu({
		height: 30,
		showTopLevelArrows: true,
		theme: 'darkblue'
	});
});

/********************************************************************************************************************/

// Resets Form back to Header level after submitting and properly saving an Add or Edit Documentation

function reset(responseText, statusText, xhr, $form)  {
	$("div#notificationContent").html(responseText.responseMessage);
	$("#messageNotification").jqxNotification({
		width: '100%',
		position: "top-right",
		opacity: 0.9,	 
		autoOpen: false,
		closeOnClick: false,
		animationOpenDelay: 800,
		autoClose: true,
		autoCloseDelay: responseText.autoCloseDelay,
		template: responseText.responseType
	});
     $("#messageNotification").jqxNotification("open");	
	if (responseText.responseType == "success") {
		$("div#divDBSelector").html("");
		$("#messageNotification").on("close", function () {
			var host = "http://" + document.location.hostname;
			window.location.replace(host);
		 });
	}
	return false;
}
