$(document).ready(function() {
	console.log("ready!");
	getBlockRooms();


	$("#block-form").submit(function(event) {
		event.preventDefault();
		blockRoom()
	});

	$("#block_start_date").datepicker({
		dateFormat: "yy-mm-d",
	});

	$("#block_end_date").datepicker({
		dateFormat: "yy-mm-d",
	});
});


function blockRoom() {

	var checkInDate = new Date($("#block_start_date").val());
	var checkOutDate = new Date($("#block_end_date").val());

	$("#block_error_message_div").addClass("display-none");
	$("#block_success_message_div").addClass("display-none");

	if (checkInDate >= checkOutDate) {
		$("#block_error_message").text("Check out should be greater than check in")
		$("#block_error_message_div").removeClass("display-none");
		$("#block_success_message_div").addClass("display-none");
		return;
	}

	var postData = $("#block-form").serialize();

	$("body").addClass("loading");

	$.post("operations/reservations/blockroom.php", postData, function(data) {
		$("body").removeClass("loading");
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$("#block_success_message").text(jsonObj.result_desciption)
			$("#block_error_message_div").addClass("display-none");
			$("#block_success_message_div").removeClass("display-none");
			getcalendar("future");
			getBlockedRooms();
		} else {
			$("#block_error_message").text(jsonObj.result_desciption)
			$("#block_error_message_div").removeClass("display-none");
			$("#block_success_message_div").addClass("display-none");
			return;
		}

	});

}


function getBlockRooms() {


	$("#block_rooms_select").load("operations/lookup/getrooms.php?status=publish&content-type=html", function() {

	});

}




