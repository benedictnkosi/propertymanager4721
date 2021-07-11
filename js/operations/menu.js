$(document).ready(function() {
	//updateView("cleaning");
	updateView("calendar");

	$("#menu_create_invoice").click(function(event) {
		localStorage.setItem("property_manager_action", "create");
		$("#submit_create_invoice").prop("value", "Create Invoice");
		$("#header_create_invoice").html("Create New Invoice");
		updateView("new-invoice");
		//in case it was disabled by stayover and checkout update
		$("#rooms_select").prop('disabled', false);
		$("#checkin_date").prop('disabled', false);
	});
});


function showInvoices() {
	$(".dashboard-div").addClass("display-none");
	$(".invoices-div").removeClass("display-none");
}



function updateView(selectedDiv) {
	$(".toggleable").addClass("display-none");
	$("#div-" + selectedDiv).removeClass("display-none");
}