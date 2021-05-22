<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/email_template.php');
require_once (__DIR__ . "/../utils/mail.php");

sendReviewRequest();

function sendReviewRequest()
{
    $checkOuts = getcheckouts();

    //print_r($checkOuts);
    if (!isset($checkOuts["result_code"])) {
        $count = 0;
        foreach ($checkOuts as &$checkOut) {
        
            if(!sendEmail($checkOut["email"], $checkOut["guest_name"])){
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to email request"
                );
                echo json_encode($temparray1);
            }else{
                $temparray1 = array(
                    'result_code' => 0,
                    'result_desciption' => "Requests Sent"
                );
                echo json_encode($temparray1);
            }
            
            $count++;
        }
        
        
        $temparray1 = array(
            'count' => $count,
            'result_code' => 1,
            'result_desciption' => "Success"
        );
        echo json_encode($temparray1);
    }else{
        $temparray1 = array(
            'count' => "0",
            'result_code' => 1,
            'result_desciption' => "No checkouts"
        );
        echo json_encode($temparray1);
    }
}

function sendEmail($to, $guestName)
{
    try {

        $Parameters = array(
            "customer_name" => $guestName
        );

        $body = generate_email_body("Review", $Parameters);

        $body = wordwrap($body, 70);

         //echo $body;
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . "info@renuga.co.za" . "\r\n";
        $headers .= 'Reply-To: ' . "info@renuga.co.za" . "\r\n";

        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

        if (strcasecmp($_SERVER['SERVER_NAME'], "localhost") == 0) {
            return true;
        } else {
            if (mail($to, "Renuga Guest House Review", $body, $headers)) {
                return true;
            } else {
                return false;
            }
        }
    } catch (Exception $e) {
        return false;
    }
}

function getcheckouts()
{
    $return_array = array();

    $sql_todays_checkouts = "SELECT wpky_hb_resa.id, accom_id, paid, price, post_title, status, admin_comment, origin, check_in, check_out, info, origin_url, received_on
FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE
`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
and wpky_posts.ID = `wpky_hb_resa`.accom_id
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))
and  origin ='website'
and email NOT LIKE '%noemail%'
and DATE(check_out) = DATE(NOW())
        and admin_comment not like '%Not available%'
order by `check_in`";

    $result = querydatabase($sql_todays_checkouts);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'count' => "0",
            'result_code' => 1,
            'result_desciption' => "No checkouts"
        );
        return $temparray1;
    } else {
        while ($results = $result->fetch_assoc()) {
            $jsonObj = json_decode($results["info"]);

            $temparray1 = array(
                'guest_name' => $jsonObj->first_name,
                'email' => $jsonObj->email,
                'result_code' => 0,
                'result_desciption' => "success"
            );
            array_push($return_array, $temparray1);
        }
        return $return_array;
    }
}

