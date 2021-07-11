$(document).ready(function() {
	getInvoices();
});


function showInvoices() {
	$(".dashboard-div").addClass("display-none");
	$(".invoices-div").removeClass("display-none");
}



function showDashboard() {
	$(".dashboard-div").removeClass("display-none");
	$(".invoices-div").addClass("display-none");
}

function updateInvoicePaid(event) {
	var data = {};
	var paid = localStorage.getItem("property_manager_paid");

	if (paid.localeCompare(event.target.value) == 0) {
		return;
	}

	$(".flexible").addClass("display-none");
	$("#invoice_success_message_div_" + event.target.id).addClass("display-none");
	$("#invoice_error_message_div_" + event.target.id).addClass("display-none");

	if (isNaN(event.target.value)) {
		$("#invoice_error_message_" + event.target.id).text("Value is not a number")
		$("#invoice_message_div_" + event.target.id).removeClass("display-none");
		return;
	}

	data["field"] = "paid";
	data["new_value"] = event.target.value;
	data["reservation_id"] = event.target.id.replace("paid_", "");


	 $("body").addClass("loading"); 
	$.post("operations/reservations/updatereservation.php", data, function(response) {
		$("body").removeClass("loading"); 
		$("#invoice_message_div_" + event.target.id).removeClass("display-none");

		var jsonObj = jQuery.parseJSON(response);
		if (jsonObj.result_code == 0) {
			getcalendar("future");
			getReservations("future");
			getBlockedRooms();
			
			$("#invoice_success_message_" + event.target.id).text(jsonObj.result_desciption)
			$("#invoice_success_message_div_" + event.target.id).removeClass("display-none");
		} else {
			$("#invoice_error_message_" + event.target.id).text(jsonObj.result_desciption)
			$("#invoice_error_message_div_" + event.target.id).removeClass("display-none");

			event.target.value = localStorage.getItem("property_manager_paid");
		}
	});
}


function deleteInvoice(event) {
	var data = {};

	$("#invoice_error_message_div_" + event.target.id).addClass("display-none");

	data["field"] = "status";
	data["new_value"] = "cancelled";
	data["reservation_id"] = event.target.id.replace("delete_invoice_", "");

	$.post("operations/reservations/updatereservation.php", data, function(response) {
		var jsonObj = jQuery.parseJSON(response);
		if (jsonObj.result_code == 0) {
			getInvoices();
		} else {
			$("#invoice_error_message_" + event.target.id).text(jsonObj.result_desciption)
			$("#invoice_error_message_div_" + event.target.id).removeClass("display-none");
		}
	});
}


function updateInvoice(event) {
	var data = {};
	var className = $(event.target).attr('class');

	$("#invoice_error_message_div_" + event.target.id).addClass("display-none");

	data["reservation_id"] = event.target.id.replace("edit_invoice_", "");

	const article = document.querySelector('#edit_invoice_' + data["reservation_id"]);


	localStorage.setItem("property_manager_action", data["reservation_id"]);

	$("#userNumber").val(article.dataset.phone);
	$("#userName").val(article.dataset.guest_name);
	$("#userEmail").val(article.dataset.email);
	$("#checkin_date").val(article.dataset.checkin);
	$("#checkout_date").val(article.dataset.checkout);
	$("#res_notes").val(article.dataset.admin_comment);
	$("#rooms_select").val(article.dataset.accom_id);
	
	if(className.includes("stayover")|| className.includes("checkout")){
		$("#rooms_select").prop('disabled', 'disabled');
		$("#checkin_date").prop('disabled', 'disabled');
	}else{
		$("#rooms_select").prop('disabled', false);
		$("#checkin_date").prop('disabled', false);
	}
	
	getRoomPrice()
	updateView("new-invoice");
	calculateNumberOfNights();
	calculateTotal();
	
	$("#header_create_invoice").html("Update Invoice - " + data["reservation_id"]);
	$("#submit_create_invoice").prop("value", "Update Invoice");
}




function viewQuotation(event) {
	window.location.href = '/propertymanager/quotation.html?res_id=' + event.target.id.replace("view_quotation_", "");
}



function getInvoices() {
	$("#invoice-list").load("operations/reservations/getinvoices.php", function() {
		$(".paid_amount").blur(function(event) {
			updateInvoicePaid(event);
		});

		$(".delete_invoice").click(function(event) {
			deleteInvoice(event);
		});

		$(".edit_invoice").click(function(event) {
			updateInvoice(event);
		});

		$(".paid_amount").focusin(function(event) {
			localStorage.setItem("property_manager_paid", event.target.value);
		});

		$(".view_quotation").click(function(event) {
			viewQuotation(event);
		});

	});
}