$(document).ready(function() {
	console.log("ready!");
	getCalendar("future");
	getRooms();



	$("#checkin_date").datepicker({
		onSelect: function(dateText) {
			calculateNumberOfNights();
		},
		dateFormat: "yy-mm-d",

	});

	$("#checkout_date").datepicker({
		dateFormat: "yy-mm-d",
		onSelect: function(dateText) {
			calculateNumberOfNights();
		},
	});


	$("#new-res-form").validate({
		rules: {
			userEmail: {
				required: true,
				email: true
			},
			userNumber: {
				required: true,
				minlength: 10,
				maxlength: 10,
				digits: true
			},
			checkin_date: {
				required: true,
				date: true
			},
			checkout_date: {
				required: true,
				date: true
			},
		}
	});


	$("#userNumber").blur(function() {
		if ($("#userNumber").val().length == 10) {
			$.get("operations/lookup/getcustomer.php?phone_number=" + $("#userNumber").val(), function(data, status) {
				var jsonObj = jQuery.parseJSON(data);
				if (jsonObj.result_code == 0) {
					$('#userName').val(jsonObj.guest_name);
					$('#userEmail').val(jsonObj.guest_email);
				}
			});
		}

	});



});


function calculateTotal() {
	//get value for check in and checkout dates
	var checkInDate = new Date($("#checkin_date").val());
	var checkOutDate = new Date($("#checkout_date").val());

	if (checkInDate >= checkOutDate || $("#rooms_select")[0].selectedIndex == 0 || $("#checkin_date").val().length < 1 || $("#checkout_date").val().length < 1) {
		return;
	}
	
	var numberOfNights = parseInt($("#number_of_night").text());
	var pricePerNight = parseInt($("#price_per_night").text());
	
	$("#total_due").text(numberOfNights * pricePerNight);

}

function createReservation(form) {
	var checkInDate = new Date($("#checkin_date").val());
	var checkOutDate = new Date($("#checkout_date").val());

	$("#error_message_div").addClass("display-none");
	$("#success_message_div").addClass("display-none");

	if (checkInDate >= checkOutDate) {
		$("#error_message").text("Check out should be greater than check in")
		$("#error_message_div").removeClass("display-none");
		$("#success_message_div").addClass("display-none");
		return;
	}

	if ($("#rooms_select")[0].selectedIndex == 0) {

		$("#error_message").text("Please select room")
		$("#error_message_div").removeClass("display-none");
		$("#success_message_div").addClass("display-none");
		return;
	}

	var postData = $("#new-res-form").serialize();


	$.post("operations/reservations/createreservation.php", postData, function(data) {
		var jsonObj = jQuery.parseJSON(data);
		if (jsonObj.result_code == 0) {
			$("#success_message").text(jsonObj.result_desciption)
			$("#error_message_div").addClass("display-none");
			$("#success_message_div").removeClass("display-none");
		}

	});

}


function getRooms() {
	$("#rooms_select").load("operations/lookup/getrooms.php?status=publish&content-type=html", function() {
		$("#rooms_select").change(function() {
			$.get("operations/lookup/getroomprice.php?accom_id=" + $("#rooms_select").val(), function(data) {
				var jsonObj = jQuery.parseJSON(data);
				if (jsonObj.result_code == 0) {
					$("#price_per_night").text(jsonObj.price);
					calculateTotal();
				}
			});
		});
	});

}


function calculateNumberOfNights() {
	//get value for check in and checkout dates
	var checkInDate = new Date($("#checkin_date").val());
	var checkOutDate = new Date($("#checkout_date").val());

	if (checkInDate >= checkOutDate) {
		return;
	}

	if ($("#checkin_date").val().length > 0 && $("#checkout_date").val().length > 0) {
		const diffTime = Math.abs(checkInDate - checkOutDate);
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
		$("#number_of_night").text(diffDays);
		calculateTotal();
	}

}


