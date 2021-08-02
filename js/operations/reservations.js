$(document).ready(function() {
	console.log("ready!");
	getReservations("future");
	getStayOvers("stayover");
	getCheckouts("checkout");

});


function getReservations(period) {
	$("#reservations-list").load("operations/reservations/getreservations.php?period=" + period, function() {
		$(".changeBookingStatus").click(function(event) {
			changeBookingStatus(event);
		});

		$(".edit_invoice").click(function(event) {
			updateInvoice(event);
		});

		$( '.image_verified' ).off();
		$('.image_verified').on('click', function(event) {
			var resID = event.target.id.replace("img_upload_", "");
			$(".uploadImageInput").click();
			$("#customer_image_id").val(resID);
		});
		
		$( '.uploadImageInput' ).off();
		$('.uploadImageInput').on('change', function(event) {
			var myForm = event.target.form;
			let formData = new FormData(myForm);
			$.ajax({
				url: "operations/utils/updateCustomerIdImage.php",
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData: false,
				success: function(response) {
					var jsonObj = jQuery.parseJSON(response);
					if (jsonObj.result_code == 0) {
						let current = event.target;
						let nextSibling = current.nextElementSibling;
						nextSibling.src = "images/verified.png";;
					}
				},
				ror: function(e) {
					$("#err").html(e).fadeIn();
				}
			});
		});


	});
}


function getStayOvers(period) {
	$("#stayOver-list").load("operations/reservations/getreservations.php?period=" + period, function() {

		$(".edit_invoice").click(function(event) {
			updateInvoice(event);
		});

		$( '.image_verified' ).off();
		$('.image_verified').on('click', function(event) {
			var resID = event.target.id.replace("img_upload_", "");
			$(".uploadImageInput").click();
			$("#customer_image_id").val(resID);
		});

		$( '.uploadImageInput' ).off();
		$('.uploadImageInput').on('change', function(event) {
			var myForm = event.target.form;
			let formData = new FormData(myForm);
			$.ajax({
				url: "operations/utils/updateCustomerIdImage.php",
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData: false,
				success: function(response) {
					var jsonObj = jQuery.parseJSON(response);
					if (jsonObj.result_code == 0) {
						let current = event.target;
						let nextSibling = current.nextElementSibling;
						nextSibling.src = "images/verified.png";;
					}
				},
				ror: function(e) {
					$("#err").html(e).fadeIn();
				}
			});
		});

	});
}


function getCheckouts(period) {
	$("#checkouts-list").load("operations/reservations/getreservations.php?period=" + period, function() {
		$(".edit_invoice").click(function(event) {
			updateInvoice(event);
		});

		$( '.image_verified' ).off();
		$('.image_verified').on('click', function(event) {
			var resID = event.target.id.replace("img_upload_", "");
			$(".uploadImageInput").click();
			$("#customer_image_id").val(resID);
		});
		
		$( '.uploadImageInput' ).off();
		$('.uploadImageInput').on('change', function(event) {
			var myForm = event.target.form;
			let formData = new FormData(myForm);
			$.ajax({
				url: "operations/utils/updateCustomerIdImage.php",
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData: false,
				success: function(response) {
					var jsonObj = jQuery.parseJSON(response);
					if (jsonObj.result_code == 0) {
						let current = event.target;
						let nextSibling = current.nextElementSibling;
						nextSibling.src = "images/verified.png";;
					}
				},
				ror: function(e) {
					$("#err").html(e).fadeIn();
				}
			});
		});

	});
}

function changeBookingStatus(event) {
	var data = {};
	var newButtonText = "";
	data["field"] = "status";

	var className = $('#' + event.target.id).attr('class');

	if (className.includes("glyphicon-triangle-top")) {
		data["new_value"] = "pending";
		$('#' + event.target.id).toggleClass("glyphicon-triangle-top");
		$('#' + event.target.id).toggleClass("glyphicon-triangle-bottom");
	} else if (className.includes("glyphicon-triangle-bottom")) {
		data["new_value"] = "confirmed";
		$('#' + event.target.id).toggleClass("glyphicon-triangle-top");
		$('#' + event.target.id).toggleClass("glyphicon-triangle-bottom");
	} else if (className.includes("glyphicon-remove")) {
		data["new_value"] = "cancelled";
		$('#' + event.target.id).toggleClass("glyphicon-remove");
		$('#' + event.target.id).toggleClass("glyphicon-ok");
	} else if (className.includes("glyphicon-ok")) {
		data["new_value"] = "confirmed";
		$('#' + event.target.id).toggleClass("glyphicon-remove");
		$('#' + event.target.id).toggleClass("glyphicon-ok");
	}

	if (className.includes("glyphicon-triangle")) {
		data["reservation_id"] = event.target.id.replace("changeBookingStatus_", "");
	} else {
		data["reservation_id"] = event.target.id.replace("cancelBooking_", "");
	}


	$("body").addClass("loading");
	$.post("operations/reservations/updatereservation.php", data, function(response) {
		$("body").removeClass("loading");
		var jsonObj = jQuery.parseJSON(response);
		if (jsonObj.result_code == 0) {
			$("#" + event.target.id).val(newButtonText);
			getcalendar("future");
		}

	});
}