$(document).ready(function() {
	console.log("ready!");
	getcalendar("future");

});


function getcalendar(period) {
	$("#calendar-table").load("operations/reservations/getcalendar.php", function() {
		$(".image_checkin").click(function(event) {
			jumpToBooking(event);
		});
	});

}


function jumpToBooking(event) {
	$reservation_id= event.target.id.replace("img_calendar_", "");
	updateView("upcoming-reservations");
	$([document.documentElement, document.body]).animate({
        scrollTop: $("a:contains('" +$reservation_id +"')").offset().top
    }, 2000);
	$("a:contains('" +$reservation_id +"')").fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500);
	
}



