<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/sms.php');

sendReviewRequest();

function sendReviewRequest()
{
    $checkOuts = getcheckouts();

    //print_r($checkOuts);
    if (!isset($checkOuts["result_code"])) {
        $count = 0;
        foreach ($checkOuts as &$checkOut) {
        
            if(!sendSMS($checkOut["phone"], $checkOut["guest_name"])){
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

function sendSMS($customerPhone, $guestName)
{
    try {

        $messageBody = "Thank You " . $guestName . ". Please take a few seconds to give us a 5-star review on Google. https://g.page/r/CVWFT5sx0AcPEAg/review. Aluve GH";        
        
        $formatedCustomerNumber = $customerPhone;
        if (strpos($customerPhone, '+27') == false) {
            $formatedCustomerNumber = '+27' . substr($customerPhone, 1);
        }
        
        $messages = array(
            array("from"=>COMPANY_PHONE_NUMBER,"to"=>$formatedCustomerNumber, "body"=>$messageBody)
        );
        
        if (strcasecmp($_SERVER['SERVER_NAME'], "localhost") == 0) {
            echo $messageBody;
            return true;
        }else{
            $result = send_message( json_encode($messages));
            if ($result['http_status'] != 201) {
                return false;
            }else{
                return true;
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

            $jsonObj = json_decode($results["info"]);
            
            $temparray1 = array(
                'guest_name' => $jsonObj->first_name,
                'phone' => $jsonObj->phone,
                'result_code' => 0,
                'result_desciption' => "success"
            );
            array_push($return_array, $temparray1);
        }
        return $return_array;
    }
}

