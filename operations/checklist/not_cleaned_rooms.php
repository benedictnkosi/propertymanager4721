<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/sms.php');
require_once (__DIR__ . "/../utils/mail.php");

checkIfAllRoomsAreCleaned();


function checkIfAllRoomsAreCleaned(){
    $sql_checkOuts_reservations = "SELECT wpky_hb_resa.id, accom_id, post_title, status, admin_comment, origin, check_in, check_out
FROM `wpky_hb_resa`, wpky_posts WHERE
wpky_posts.ID = `wpky_hb_resa`.accom_id
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')))
and DATE(check_out) = DATE(NOW())
and admin_comment not like '%Not available%'
order by `check_in`";
    
    echo $sql_checkOuts_reservations;
    
    $result = querydatabase($sql_checkOuts_reservations);
    $rsType = gettype($result);
    
    echo "count - " .mysqli_num_rows ( $result );
    if (strcasecmp($rsType, "string") == 0) {
        echo 'no checkouts today';
        exit();
    } else {
        $roomsToCleanArray = array();
        while ($results = $result->fetch_assoc()) {
            echo "accom - " . $accomId;
            $accomId = $results["accom_id"];
            if(isRoomCleaned($accomId) == false){
                array_push($roomsToCleanArray, $results["post_title"]);
            }
        }
        
        if(!empty($roomsToCleanArray)){
            $messageBody = "Rooms not cleaned: ";
            foreach ($roomsToCleanArray as &$room) {
                echo "-room" . $room;
                $messageBody = $messageBody . $room . ", ";
            }
            sendEmail($messageBody);
            echo $messageBody;
        }else{
            echo "all rooms are cleaned";
        }
    }
}

function isRoomCleaned($accomId){
    $sql_cleaning_checklist = "SELECT * FROM `completed_checklist`
where `accom_id` = {accom_id}
and DATE(`timestamp`) = DATE(NOW())";
    
    $sql = str_replace("{accom_id}",$accomId,$sql_cleaning_checklist);
    $result = querydatabase($sql);
    $rsType = gettype($result);
    
    if (strcasecmp($rsType, "string") == 0) {
        return false;
    } else {
        return true;
    }
}


function sendEmail($messageBody)
{
    try {

        $body = wordwrap($messageBody, 70);
        
        // echo $body;
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . EMAIL_ADDRESS . "\r\n";
        $headers .= 'Reply-To: ' . EMAIL_ADDRESS . "\r\n";
        
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        
        if (strcasecmp($_SERVER['SERVER_NAME'], "localhost") == 0) {
            return true;
        } else {
            if (mail(EMAIL_ADDRESS, "Rooms Not Cleaned - " . date("Y/m/d"), $body, $headers)) {
                return true;
            } else {
                return false;
            }
        }
    } catch (Exception $e) {
        return false;
    }
}

