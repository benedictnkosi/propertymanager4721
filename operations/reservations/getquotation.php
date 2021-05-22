<?php

include_once '../../utils/data.php';

require_once '../lookup/getroomprice.php';



if (isset($_GET["getquotation"])) {

    getQuotation();

} else {

    $temparray1 = array(

        'result_code' => 1,

        'result_desciption' => "Please provide id"

    );

    echo serialize($temparray1);

}



function getQuotation()

{

    $return_array = array();



    $sql = "SELECT wpky_hb_resa.id, accom_id, post_title, status, price, paid, admin_comment, origin, check_in, check_out, info, origin_url, received_on

FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE

`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`

and wpky_posts.ID = `wpky_hb_resa`.accom_id

and wpky_hb_resa.id = " . $_GET["getquotation"];



    $result = null;



    $result = querydatabase($sql);



    $rsType = gettype($result);



    if (strcasecmp($rsType, "string") == 0) {

        echo '<div class="res-details">

						<h4 class="guest-name">No invoices found</h4>

					</div>';

        exit();

    } else {



        while ($results = $result->fetch_assoc()) {

            $guestName = "";

            $quotationDate = "";

            $contactDetails = "";



            $checkInDate = "";

            $checkOutDate = "";

            $room = "";

            $price_per_night_Jason = getRoomPriceByID($results["accom_id"]);

            $price_per_night = $price_per_night_Jason["price"];



            $total = "";
	    $paid = "";

            $resa_check_out = "";
	


            $jsonObj = json_decode($results["info"]);



            if (strcasecmp($results["origin"], "booking.com") == 0) {

                $guestName = str_replace("Summary: CLOSED - ", "", $results["admin_comment"]);

            } else if (strcasecmp($results["origin"], "Airbnb") == 0) {

                $guestName = 'Airbnb Guest';

            } else if (strcasecmp($results["origin"], "website") == 0) {

                $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;

                $contactDetails = 'Tel: ' . $jsonObj->phone . '<br>Email: ' . $jsonObj->email;

            }



            $checkInDate = new DateTime($results["check_in"]);

            $checkOutDate = new DateTime($results["check_out"]);

            $room = $results["post_title"];

            $total = $results["price"];
            $paid = $results["paid"];




            $paid = $results["paid"];

            $quotationDate = $results["received_on"];



            echo '<div style="margin-top: 1rem;">

                

					<h1>Renuga Guesthouse Quotation</h1>

                

                

					Quotation Number: ' . $_GET["getquotation"] . '<br>

					Quotation date: ' . $quotationDate . '

					<h3>

						<strong>Bill to:</strong>

					</h3>

                

					<p>

						' . $guestName . '<br>

						' . $contactDetails . '

					</p>

						    

					<p>

						Arrival Date: ' . $checkInDate->format('d') . '  ' . $checkInDate->format('M') . ' - ' . $checkInDate->format('Y') . '

						<br>Departure Date: ' . $checkOutDate->format('d') . '  ' . $checkOutDate->format('M') . ' - ' . $checkOutDate->format('Y') . '

					</p>

				</div>

				

				<br />

				<table id="customers" class="table table-striped" width="100%">

						    

					<thead width="100%" width="100%">

						<tr class=\'warning\'>

						    

							<th>Room</th>

							

							<th>Per Night</th>

							<th>Total</th>
<th>Paid</th>
						    

						</tr>

					</thead>

					<tbody width="100%">

						<tr>

						    

							<td>' . $room . '</td>

							<td>R' . number_format($price_per_night, 2, '.', '') . '</td>

							<td>R' . number_format($total, 2, '.', '')  . '</td>

						    <td>R' . number_format($paid, 2, '.', '')  . '</td>



						</tr>

						    

					</tbody>

				</table>

';

        }

    }

}



