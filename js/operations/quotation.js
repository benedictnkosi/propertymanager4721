$(document).ready(function() {
	getQuotation();
});


function getQuotation() {
	$("#quotation_dynamic_values").load("operations/reservations/getquotation.php?getquotation=" + getUrlParameter("res_id"), function() {
		
	});
}

function getUrlParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
	return false;
}

