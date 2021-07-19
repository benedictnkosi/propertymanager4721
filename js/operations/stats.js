$(document).ready(function() {
	console.log("ready!");
	getStatsForToday();
	getOverallOccupancy("30", "overall-30-occupancy");

	//get day of month
	var d = new Date();
	var date = d.getDate();
	getOverallOccupancy(date, "overall-month-occupancy");
	getOccupancyPerRoom("30");


	$(".glyphicon-log-in").click(function() {
		$([document.documentElement, document.body]).animate({
			scrollTop: $("#reservations-list").offset().top
		}, 2000);
	});

	$(".glyphicon-log-out").click(function() {
		$([document.documentElement, document.body]).animate({
			scrollTop: $("#checkouts-list").offset().top
		}, 2000);
	});

	$(".glyphicon-briefcase").click(function() {
		$([document.documentElement, document.body]).animate({
			scrollTop: $("#stayOver-list").offset().top
		}, 2000);
	});


});


function getStatsForTomorrow() {


	$('#today-tomorrow').text("TOMORROW");
	getcheckins("tomorrow");
	getcheckouts("tomorrow");
	getstayovers("tomorrow");
}


function getStatsForToday() {
	$('#today-tomorrow').text("TODAY");

	getcheckins("today");
	getcheckouts("today");
	getstayovers("today");
}

function getcheckins(period) {
	$.get("operations/stats/getcheckins.php?period=" + period, function(data, status) {
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$('#stats_checkin_count').text(jsonObj.count);
		}
	});
}


function getcheckouts(period) {
	$.get("operations/stats/getcheckouts.php?period=" + period, function(data, status) {
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$('#stats_checkout_count').text(jsonObj.count);
		}
	});
}


function getstayovers(period) {
	$.get("operations/stats/getstayovers.php?period=" + period, function(data, status) {
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$('#stats_overstays_count').text(jsonObj.count);
		}
	});
}


function getOverallOccupancy(period, elementId) {
	$.get("operations/stats/getoccupancy.php?days=" + period + "&type=overall", function(data, status) {
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$('#' + elementId).text(jsonObj.occupancy);
		}
	});
}

function getOccupancyPerRoomForMonth() {
	var myDate = new Date();
	var dayOfmonth = myDate.getDate();
	getOccupancyPerRoom(dayOfmonth)

}


function getOccupancyPerRoom(period) {
	$("#occupancy-div").load("operations/stats/getoccupancy.php?days=" + period + "&type=room", function() {
	});

}




