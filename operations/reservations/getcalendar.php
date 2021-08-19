<?php
require_once (__DIR__ . '/../utils/data.php');

;
getCalendar();

function getCalendar()
{
    $rooms_array = array();
    $numberOfDays = 60;

    $rooms_array = getRoomIDs('publish');

    echo '<tr><th></th>';

    for ($x = 0; $x <= $numberOfDays; $x ++) {
        $todayDate = new DateTime();
        $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));
        if (strcmp($tempDate->format('d'), "01") == 0) {
            
            if (strcmp($tempDate->format('D'), "Sat") == 0 || strcmp($tempDate->format('D'), "Sun") == 0) {
                echo '<th class="new-month weekend">' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            }else{
                echo '<th class="new-month">' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            }
            
        }else{
            if (strcmp($tempDate->format('D'), "Sat") == 0 || strcmp($tempDate->format('D'), "Sun") == 0) {
                echo '<th class="weekend">' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            }else{
                echo '<th>' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            }
        }
        
    }
    echo '</tr>';
    echo "in here";

    foreach ($rooms_array as &$roomArray) {
        
        
        

        echo '<tr><th class="headcol">' . $roomArray["room_short_name"] . '</th>';

        $sql_upcoming_reservations = "SELECT wpky_hb_resa.id, accom_id, post_title, check_in, check_out, status, info, origin, admin_comment     
        FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE
        `wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
        and wpky_posts.ID = `wpky_hb_resa`.accom_id
        and accom_id = " . $roomArray['ID'] . "
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))
            
        and DATE(check_in) <= DATE(NOW()) + INTERVAL 180 DAY
        and DATE(check_out) > DATE(NOW())
        order by `check_in`";



        $sql_upcoming_blocks = "SELECT accom_id, from_date, to_date, comment
FROM wpky_hb_accom_blocked
where DATE(from_date) <= DATE(NOW()) + INTERVAL 30 DAY
and DATE(to_date) > DATE(NOW())
and accom_id = " . $roomArray['ID'];

        $resultReservations = querydatabase($sql_upcoming_reservations);
         echo $sql_upcoming_reservations;
        $rsType = gettype($resultReservations);

        $resultBlocks = querydatabase($sql_upcoming_blocks);
        $rsTypeBlocks = gettype($resultBlocks);

        // echo $sql_upcoming_reservations;
        // echo "rstype" .$rsType;
        if (strcasecmp($rsType, "string") == 0 && strcasecmp($rsTypeBlocks, "string") == 0) {
            for ($x = 0; $x <= $numberOfDays; $x ++) {
                echo '<td class="available"></td>';
            }
        } else {

            // echo "test";

            $reservations_array = array();
            $blocks_array = array();
            $guestName = "";
            if (strcasecmp($rsType, "string") !== 0) {
                while ($resultReservation = $resultReservations->fetch_assoc()) {
                    $jsonObj = json_decode($resultReservation["info"]);
                    
                    if (strcasecmp($resultReservation["origin"], "booking.com") == 0) {
                        $guestName = str_replace("Summary: CLOSED - ", "", $resultReservation["admin_comment"]);
                    } else if (strcasecmp($resultReservation["origin"], "Airbnb") == 0) {
                        $guestName = 'Airbnb Guest';
                    } else if (strcasecmp($resultReservation["origin"], "website") == 0) {
                        $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;
                    }
                    
                    $reservations = array(
                        'check_in' => $resultReservation["check_in"],
                        'check_out' => $resultReservation["check_out"],
                        'status' => $resultReservation["status"],
                        'name' => $guestName,
                        'id' => $resultReservation["id"]
                        
                    );
                    array_push($reservations_array, $reservations);
                }
            }

            if (strcasecmp($rsTypeBlocks, "string") !== 0) {
                while ($resultBlock = $resultBlocks->fetch_assoc()) {
                    $blocks = array(
                        'from_date' => $resultBlock["from_date"],
                        'to_date' => $resultBlock["to_date"],
                        'comment' => $resultBlock["comment"]
                        
                    );
                    array_push($blocks_array, $blocks);
                }
            }

            for ($x = 0; $x <= $numberOfDays; $x ++) {
                $todayDate = new DateTime();
                $letter = "";
                // echo 'x is : ' . $x. '<br>';
                $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));
                $isDatebooked = false;
                
                $guestName = "";
                $checkin = "";
                $checkout = "";
                $resID = "";
                $roomName = "";
                $blockNote = "";

                $isDateBlocked = false;
                $isDateBookedButOpen = false;
                $isItTheFirst = false;
                
                foreach ($reservations_array as &$accomodation_array) {
                    $letter = substr($accomodation_array["name"], $x, 1);
                    
                    $isCheckInday = false;
                    $islastNightDay = false;
                    
                    if ($tempDate >= new DateTime($accomodation_array["check_in"]) && $tempDate < new DateTime($accomodation_array["check_out"])) {
                        if (strcasecmp($accomodation_array["status"], "confirmed") == 0) {
                            $isDatebooked = true;
                            $checkin = $accomodation_array["check_in"];
                            $checkout = $accomodation_array["check_out"];
                            $guestName = $accomodation_array["name"];
                            $resID = $accomodation_array["id"];
                            
                            $dateCheckIn = new DateTime($accomodation_array["check_in"]);
                            $strtempDate = $tempDate->format('d') . $tempDate->format('m') . $tempDate->format('Y') ;
                            $strCheckindate = $dateCheckIn->format('d') . $dateCheckIn->format('m') . $dateCheckIn->format('Y') ;
                            
                            
                           
                            
                            if (strcmp($strtempDate, $strCheckindate) == 0) {
                                $isCheckInday = true;
                               // echo "Check in <br>";
                            }
                           // echo "<br>";
                            
                            break;
                        
                        } else if (strcasecmp($accomodation_array["status"], "pending") == 0) {
                            $isDateBookedButOpen = true;
                            break;
                        }
                    }
                }
                
                //echo "letter is " . $letter . "#####";

                
                
                foreach ($blocks_array as &$block_array) {
                    if ($tempDate >= new DateTime($block_array["from_date"]) && $tempDate < new DateTime($block_array["to_date"])) {
                        $isDateBlocked = true;
                        $blockNote = $block_array["comment"];
                        
                        break;
                    }
                }

                if ($isDatebooked) {
                    if($isCheckInday == true){
                        echo '<td  class="booked checkin" resid="' .$resID . '" title="' . $guestName .'"><img  src="images/checkin.png"  resid="' .$resID . '" alt="checkin" class="image_checkin"></td>';
                    }else{
                        echo '<td  class="booked" resid="' .$resID . '" title="' . $guestName .'"></td>';
                    }
                  
                        
                } else if ($isDateBlocked) {
                    echo '<td class="blocked" title="' . $blockNote .'"></td>';
                } else if ($isDateBookedButOpen) {
                    echo '<td class="pending"></td>';
                } else {
                    echo '<td class="available"></td>';
                }
            }
        }

        echo '</tr>';
    }
}

function getRoomIDs($status)
{
    $rooms_array = array();

    $sql = "SELECT ID, post_title, room_short_name
FROM wpky_posts, room_short_names
where room_short_names.accom_id = wpky_posts.ID
 and post_type = 'hb_accommodation'
        and post_status = '" . $status . "';";

echo $sql;
    $result = querydatabase($sql);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "No results found"
        );
        echo serialize($temparray1);
        exit();
    } else {
        while ($results = $result->fetch_assoc()) {
            $temparray1 = array(
                'ID' => $results["ID"],
                'room_short_name' => $results["room_short_name"]
            );
            array_push($rooms_array, $temparray1);
        }
    }

    return $rooms_array;
}
