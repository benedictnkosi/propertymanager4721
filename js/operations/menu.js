

$(document).ready(function() {
	
	if (sessionStorage.getItem("current_page") === null) {
		updateView("calendar");
	} else {
		updateView(sessionStorage.getItem("current_page"));
	}

	$(".create_invoice_menu").click(function(event) {
		localStorage.setItem("property_manager_action", "create");
		$("#submit_create_invoice").prop("value", "Create Invoice");
		$("#header_create_invoice").html("Create New Invoice");
		updateView("new-invoice");
		//in case it was disabled by stayover and checkout update
		$("#rooms_select").prop('disabled', false);
		$("#checkin_date").prop('disabled', false);
	});


	$(".info-input-box").click(function(event) {
		getPage();
		var copyText = event.target;
		/* Select the text field */
		copyText.select();
		copyText.setSelectionRange(0, 99999); /* For mobile devices */

		/* Copy the text inside the text field */
		document.execCommand("copy");

		/* Alert the copied text */
		//alert("Copied the text: " + copyText.value);
		var text = document.createTextNode("Copied");
		copyText.parentNode.insertBefore(text, copyText.nextSibling)

	});
	
	console.log(guid());

});


const guid = a => (a ?
    (a ^ ((16 * Math.random()) >> (a / 4))).toString(16) :
    ([1E7] + -1E3 + -4E3 + -8E3 + -1E11).replace(/[018]/g, guid));


function showInvoices() {
	$(".dashboard-div").addClass("display-none");
	$(".invoices-div").removeClass("display-none");
}

function getPage() {
	
}


function updateView(selectedDiv) {
	$(".toggleable").addClass("display-none");
	$("#div-" + selectedDiv).removeClass("display-none");
	sessionStorage.setItem("current_page", selectedDiv);
}


function toggleMenu() {
	var x = document.getElementById("myLinks");
	if (x.style.display === "block") {
		x.style.display = "none";
	} else {
		x.style.display = "block";
	}
}




