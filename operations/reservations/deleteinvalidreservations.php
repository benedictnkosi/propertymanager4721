<?php

require_once (__DIR__ . '/../utils/data.php');


deleteReservations();





function deleteReservations()

{

    $sqlDeleteAirbnb = "delete from wpky_hb_resa 

where origin = 'Airbnb'

and admin_comment LIKE '%Summary: Airbnb %'

and origin_url = ''";

    

    $sqlDeleteBookingCom = "delete from wpky_hb_resa

where origin = 'booking.com'

and admin_comment LIKE '%Summary: CLOSED - Not Available%'

and origin_url = ''";

    

    $sqlDeleteCancelled = "delete from wpky_hb_resa

where status = 'cancelled'";


	$sqlDeleteBlockedRooms = "delete from wpky_hb_accom_blocked

where DATE(from_date) >= DATE(NOW()) + INTERVAL 90 DAY
or DATE(to_date) >= DATE(NOW()) + INTERVAL 180 DAY
or DATE(to_date)< DATE(NOW()) - INTERVAL 30 DAY";


    

    //echo $sqlUpdateRes;

    $result = deleterecord($sqlDeleteAirbnb);

    $result = deleterecord($sqlDeleteBookingCom);

    $result = deleterecord($sqlDeleteCancelled);

    $result = deleterecord($sqlDeleteBlockedRooms );


    

    $temparray1 = array(

        'result_code' => 0,

        'result_desciption' => 'Successfully deleted reservations'

    );

    echo json_encode($temparray1);

    

}









