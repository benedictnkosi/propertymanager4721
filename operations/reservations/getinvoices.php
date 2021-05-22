<?php

require_once (__DIR__ . '/../utils/data.php');

getInvoicesHtml();

function getInvoicesHtml()
{
    $return_array = array();
    
    $sql = "SELECT wpky_hb_resa.id, accom_id, post_title, status, price, paid, admin_comment, origin, check_in, check_out, info, origin_url, received_on
FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE
`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
and wpky_posts.ID = `wpky_hb_resa`.accom_id
and (`status` = 'pending' or (`status` = 'confirmed' and NOT price <=> paid))
and paid < price
and DATE(check_in) <= DATE(NOW()) + INTERVAL 180 DAY
and DATE(check_in) >= DATE(NOW())
and DATE(check_out) > DATE(NOW())
and admin_comment not like '%Not available%'
order by `check_in`";
    
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
            
            $contactDetails = "";
            $jsonObj = json_decode($results["info"]);
            $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;
            
            $buttonText = "";
            if (strcasecmp($results["status"], "confirmed") == 0) {
                $buttonText = "Un-block room";
            } else if (strcasecmp($results["status"], "pending") == 0) {
                $buttonText = "Block room";
            }
            
            $checkInDate = new DateTime($results["check_in"]);
            $checkOutDate = new DateTime($results["check_out"]);
            
            $contactDetails = '<p name="guest-contact"><a href="tel:' . $jsonObj->phone . '">' . $jsonObj->phone . '</a>,
                
                    <a href = "mailto: ' . $jsonObj->email . '">' . $jsonObj->email . '</a></p>';
            
            echo '<div class="res-details">
						<h4 class="guest-name"><a target="_blank" href="/propertymanager/operations/reservations/' .$results["id"]. '.pdf">' . $guestName . ' - ' . $results["id"] . '</a></h4>
						    
<p>' . $results["post_title"] . '</p>
						<p name="res-dates">' . $checkInDate->format('M') . '  ' . $checkInDate->format('d') . ' - ' . $checkOutDate->format('d') . ', ' . $checkOutDate->format('Y') . '</p>
' . $contactDetails . '
<p>Total: ' . $results["price"] . '</p>
												<p>Paid:<input id="paid_' . $results["id"] . '" type="text"
										 class="textbox paid_amount" value="' . $results["paid"] . '"/></p>
										     
                        <p class="far-right">' . $results["received_on"] . '</p>
<p class="far-right"><span class="glyphicon glyphicon-trash delete_invoice clickable" aria-hidden="true" id="delete_invoice_' . $results["id"] . '"></span>
    
<span class="glyphicon glyphicon-edit edit_invoice clickable" aria-hidden="true" id="edit_invoice_' . $results["id"] . '" data-email="' . $jsonObj->email . '" data-guest_name="' . $guestName . '" data-phone="' . $jsonObj->phone . '" data-accom_id="' . $results["accom_id"] . '" data-checkin="'.$checkInDate->format('Y') . '-' . $checkInDate->format('m').'-' . $checkInDate->format('d').'" data-checkout="'.$checkOutDate->format('Y') . '-' . $checkOutDate->format('m').'-' . $checkOutDate->format('d'). '" data-notes="' . $results["admin_comment"] . '"></span>
    
   </p>
    
    
    
<div class="flexible display-none" id="invoice_message_div_paid_' . $results["id"] . '" >
										<div class="flex-bottom">
											<div class="flex1" id="invoice_success_message_div_paid_' . $results["id"] . '">
												<h5 id="invoice_success_message_paid_' . $results["id"] . '"></h5>
											</div>
											<div  class="flex2" id="invoice_error_message_div_paid_' . $results["id"] . '">
												<h5 id="invoice_error_message_paid_' . $results["id"] . '"></h5>
											</div>
										</div>
									</div>
												    
					</div>';
        }
    }
}