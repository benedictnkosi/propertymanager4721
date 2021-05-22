$(document).ready(function() {

	getChecklistRooms();

	$("#cleaning-checkist-form").submit(function(event) {
		event.preventDefault();
		submitCleaningChecklist(event)
	});

	$("#cleaning_date").datepicker({
		onSelect: function(dateText) {
			getChecklist();
		},
		dateFormat: "yy-mm-d",
	});

	$('#checklist_history').change(function() {
		if (this.checked) {
			$("#cleaning_date_div").removeClass("display-none");
			$("#cleaning_checklist_submit").addClass("display-none");
			$("#cleaning_notes").addClass("display-none");

			getChecklist();
		} else {
			$("#cleaning_date_div").addClass("display-none");
			$("#cleaning_checklist_submit").removeClass("display-none");
			$("#cleaning_notes").removeClass("display-none");

			getChecklist();
		}

	});

});


function getChecklistRooms() {
	$("#cleaning_rooms_select").load("operations/lookup/getrooms.php?status=publish&content-type=html", function() {
		$("#cleaning_rooms_select").change(function(event) {
			getChecklist();
			$("#cleaning_notes").val("");

		});
	});
}



function getChecklist() {
	accom_id = $('#cleaning_rooms_select').val();
	sessionStorage.setItem("accom_id", accom_id);
	checklist_history = "no";
	var cleaningDate = $("#cleaning_date").val();

	if ($('#checklist_history:checkbox:checked').length > 0) {
		checklist_history = "yes";
	} else {
		checklist_history = "no";
	}

	$("#cleaning-checklist-div").load("operations/checklist/cleaning_checklist.php?cleaning_checklist=" + accom_id + "&checklist_history=" + checklist_history + "&cleaning_date=" + cleaningDate, function() {

	});
}


function submitCleaningChecklist(event) {

	$("#cleaning-checkist_error_message_div").addClass("display-none");
	$("#cleaning-checkist_success_message_div").addClass("display-none");


	var lengthOfUnchecked = $('.cleaning_checklist:checkbox:not(:checked)').length;
	if (lengthOfUnchecked > 0 && $('#cleaning_notes').val().length < 5) {
		$("#cleaning-checkist_error_message").text("Please provide notes for the items not cleaned")
		$("#cleaning-checkist_error_message_div").removeClass("display-none");
		$("#cleaning-checkist_success_message_div").addClass("display-none");
		return;
	}

	const data = new FormData(event.target);
	const value = Object.fromEntries(data.entries());
	value.topics = data.getAll("cleaning_checklist");


	$("body").addClass("loading");

	$.post("operations/checklist/cleaning_checklist.php?accom_id=" + sessionStorage.getItem("accom_id"), value, function(data) {
		$("body").removeClass("loading");
		var jsonObj = jQuery.parseJSON(data);

		if (jsonObj.result_code == 0) {
			$("#cleaning-checkist_success_message").text(jsonObj.result_desciption)
			$("#cleaning-checkist_error_message_div").addClass("display-none");
			$("#cleaning-checkist_success_message_div").removeClass("display-none");

		} else {
			$("#cleaning-checkist_error_message").text(jsonObj.result_desciption)
			$("#cleaning-checkist_error_message_div").removeClass("display-none");
			$("#cleaning-checkist_success_message_div").addClass("display-none");
			return;
		}
	});

}





