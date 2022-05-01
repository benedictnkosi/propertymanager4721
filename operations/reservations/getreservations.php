<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../stats/getstays.php');

if (isset($_GET["period"])) {

    getreservationsHtml();
} else {

    $temparray1 = array(

        'result_code' => 1,

        'result_desciption' => "Please provide period"
    );

    echo serialize($temparray1);
}

function hasGuestStayedInRoom($customerId, $accom_id){
    $sql = "SELECT wpky_hb_resa.id, wpky_hb_customers.id as customer_id, accom_id, post_title, check_in

FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE

`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`

and wpky_posts.ID = `wpky_hb_resa`.accom_id

and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))

and wpky_hb_customers.id = ".$customerId." 
and accom_id = ".$accom_id." 
and DATE(check_in) < DATE(NOW())";
    
    $result = querydatabase($sql);
    $rsType = gettype($result);
    
    if (strcasecmp($rsType, "string") == 0) {
       return false;
    }else{
        return true;
    }
}


function getreservationsHtml()

{
    $return_array = array();

    $sql_upcoming_reservations = "SELECT wpky_hb_resa.id, wpky_hb_customers.id as customer_id, accom_id, paid, price, post_title, status, admin_comment, origin, check_in, check_out, info, origin_url, received_on,customer_id, id_image 

FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE

`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`

and wpky_posts.ID = `wpky_hb_resa`.accom_id

and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))

and DATE(check_in) >= DATE(NOW())

and DATE(check_in) <= DATE(NOW()) + INTERVAL 180 DAY

and DATE(check_out) > DATE(NOW())

order by `check_in`";

    $sql_stayOver_reservations = "SELECT wpky_hb_resa.id, accom_id, paid, price, post_title, status, admin_comment, origin, check_in, check_out, info, origin_url, received_on, customer_id , id_image 

FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE

`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`

and wpky_posts.ID = `wpky_hb_resa`.accom_id

and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')))

and DATE(check_in) < DATE(NOW())     

and DATE(check_in) > DATE(NOW()) - INTERVAL 180 DAY

and DATE(check_out) > DATE(NOW())

order by `check_in`";

    $sql_checkOuts_reservations = "SELECT wpky_hb_resa.id, accom_id, paid, price, post_title, status, admin_comment, origin, check_in, check_out, info, origin_url, received_on, customer_id, id_image 

FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE

`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`

and wpky_posts.ID = `wpky_hb_resa`.accom_id

and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')))

and admin_comment not like '%Not available%'

and DATE(check_out) = DATE(NOW())

order by `check_in`";

    $checkInPeriod = $_GET["period"];

    $result = null;

    if (strcasecmp($checkInPeriod, "future") == 0) {

        $result = querydatabase($sql_upcoming_reservations);
         //echo $sql_upcoming_reservations;
    } else if (strcasecmp($checkInPeriod, "stayover") == 0) {

        $result = querydatabase($sql_stayOver_reservations);
    } else if (strcasecmp($checkInPeriod, "checkout") == 0) {

        $result = querydatabase($sql_checkOuts_reservations);
    } else {

        echo '<div class="res-details">

						<h4 class="guest-name">Please provide either past or future for period</h4>

					</div>';

        exit();
    }

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        echo '<div class="res-details">

						<h4 class="guest-name">No reservations found</h4>

					</div>';

        exit();
    } else {

        while ($results = $result->fetch_assoc()) {

            $guestName = "";

            $contactDetails = "";
            
            $hasGuestStayedInRoom = hasGuestStayedInRoom($results["customer_id"],$results["accom_id"]);

            $jsonObj = json_decode($results["info"]);

            if (strcasecmp($results["origin"], "yourporter") == 0) {
                //Summary: Double Room-Njingalwazi Dlamini-07 Oct-08 Oct

                $index = strpos($results["admin_comment"], "-") + 1;
                $lastIndex = strpos($results["admin_comment"], "-", $index );
  
                $guestName = substr($results["admin_comment"],$index,$lastIndex -  $index);
            }else if (strcasecmp($results["origin"], "booking.com") == 0) {
                
                $guestName = str_replace("Summary: CLOSED - ", "", $results["admin_comment"]);
            }else if (strcasecmp($results["origin"], "Airbnb") == 0) {
                $guestName = 'Airbnb Guest';

                $pos = strpos($results["admin_comment"], "Name:");
                if ($pos !== false) {
                    $guestName = str_replace("Name: ", "", $results["admin_comment"]);
                }
                

                $contactDetails = '<p><a href="' . $results["origin_url"] . '" target="_blank" >Reservation Details</a></p>';
            } else if (strcasecmp($results["origin"], "website") == 0) {

                $guestName = $jsonObj->first_name;

                $contactDetails = '<p name="guest-contact"><a href="tel:' . $jsonObj->phone . '">' . $jsonObj->phone . '</a>

                    </p>';
            }

            $blockClassName = "";

            if (strcasecmp($results["status"], "confirmed") == 0) {

                $blockClassName = "glyphicon-triangle-top";
            } else if (strcasecmp($results["status"], "pending") == 0) {

                $blockClassName = "glyphicon-triangle-bottom";
            }

            $checkInDate = new DateTime($results["check_in"]);

            $checkOutDate = new DateTime($results["check_out"]);
            $stays = getNumberOfStays($results["customer_id"]);
            $stayshtml = "";
            $formatedPhoneNumber = "";
            if (strcasecmp($results["origin"], "website") !== 0) {
                $stays = "";
            } else {
                $stayshtml = '<div class="stays-div">' . $stays . '</div>';
                $formatedPhoneNumber = str_replace("+27", "0", $jsonObj->phone);
            }

            echo '<div class="res-details">

						<h4 class="guest-name">' . $stayshtml . '<a target="_blank" href="/invoices/' . $results["id"] . '.pdf">' . $guestName . ' - ' . $results["id"] . '</a></h4>

						<p>' . $results["post_title"] . '</p>

						<p name="res-dates">' . $checkInDate->format('M') . '  ' . $checkInDate->format('d') . ' - ' . $checkOutDate->format('d') . ', ' . $checkOutDate->format('Y') . '</p>

						' . $contactDetails;

            if (strcasecmp($results["origin"], "website") == 0) {

                echo '<p>Total: ' . $results["price"] . '</p>';

                if (strcasecmp($results["price"], $results["paid"]) == 0) {

                    echo '<p>Paid: ' . $results["paid"] . '</p>';
                } else {

                    echo '<p class="flag-reg">Paid: ' . $results["paid"] . '</p>';
                }

                $customerIdImage;
                if (strcasecmp($results["id_image"], "Not Verified") == 0) {
                    $customerIdImage = "unverified.png";
                } else {
                    $customerIdImage = "verified.png";
                }

                echo '<img src="images/' . $customerIdImage . '" class="image_verified" id="img_upload_'.$results["customer_id"].'"/>';
            } else {
                echo '<p><img src="/propertymanager4721/images/' . $results["origin"] . '.png" class="origin_image"></img></p>';
            }

            echo '
                        <p class="far-right">' . $results["received_on"] . '</p>

<p class="far-right">';

            
            
            if (strcasecmp($results["origin"], "website") == 0) {
                if ($hasGuestStayedInRoom == true) {
                    echo '<span title="Self-Checkin Eligible" class="glyphicon glyphicon-plane" aria-hidden="true"></span>';
                }

                echo '<span title="Cancel booking" class="glyphicon glyphicon-remove changeBookingStatus clickable" aria-hidden="true" id="cancelBooking_' . $results["id"] . '"></span>
<a title="Whatsapp Guest" target="_blank" href="https://api.whatsapp.com/send?phone=+27%20' . $formatedPhoneNumber . '&text=Hello,%20this%20is%20Aluve%20Guesthouse%20:)"><i class="fa fa-whatsapp" aria-hidden="true"></i></a>
                </span>  ';
            }

            
            
            echo '<span title="Open\Close Room" class="glyphicon ' . $blockClassName . ' changeBookingStatus clickable" aria-hidden="true" id="changeBookingStatus_' . $results["id"] . '"></span>
<span title="Edit booking" class="glyphicon glyphicon-edit edit_invoice clickable ' . $checkInPeriod . '" aria-hidden="true" id="edit_invoice_' . $results["id"] . '" data-guest_name="' . $guestName . '" data-phone="' . $jsonObj->phone . '" data-accom_id="' . $results["accom_id"] . '" data-checkin="' . $checkInDate->format('Y') . '-' . $checkInDate->format('m') . '-' . $checkInDate->format('d') . '" data-checkout="' . $checkOutDate->format('Y') . '-' . $checkOutDate->format('m') . '-' . $checkOutDate->format('d') . '" data-notes="' . $results["admin_comment"] . '"></span>
<span title="Block Guest" class="glyphicon glyphicon glyphicon-ban-circle blockGuest clickable" aria-hidden="true" id="blockGuest_' . $results["customer_id"] . '"></span>
    
</p>   

						<div class="clearfix">

<div>

									

</div>





</div>

					</div>';
        }
    }
}



