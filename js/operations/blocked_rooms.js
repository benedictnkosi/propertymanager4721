$(document).ready(function() {
	console.log("ready!");
	getBlockedRooms();

});


function getBlockedRooms() {
	$("#block-list").load("operations/reservations/getblockedrooms.php", function() {
		$(".deleteBlockRoom").click(function(event) {
			deleteBlockRoom(event);
		});
	});
}

function deleteBlockRoom(event) {
	var data = {};
	var newButtonText = "";

	data["block_id"] = event.target.id.replace("delete_blocked_", "");

	$.post("operations/reservations/blockroom.php", data, function(response) {
		var jsonObj = jQuery.parseJSON(response);
		if (jsonObj.result_code == 0) {
			$("#" + event.target.id).val(newButtonText);
			getcalendar("future");
			getBlockedRooms();
		}

	});
}