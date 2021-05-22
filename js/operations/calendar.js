$(document).ready(function() {
	console.log("ready!");
	getcalendar("future");

});


function getcalendar(period) {
	$("#calendar-table").load("operations/reservations/getcalendar.php", function() {
	});

}

