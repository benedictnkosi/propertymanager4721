<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/sms.php');
require_once (__DIR__ . '/commons.php');
require_once (__DIR__ . '/blockroom.php');
require_once (__DIR__ . '/../lookup/getrespaid.php');
require_once (__DIR__ . '/../utils/invoice.php');
require_once (__DIR__ . '/../lookup/getroomprice.php');
require_once (__DIR__ . '/../app/application.php');


if (isset($_POST["field"])) {
    updateReservationField($_POST["field"], $_POST["new_value"], $_POST["reservation_id"]);
}

function updateReservationField($fieldName, $newValue, $reservationId)
{
    $return_array = array();

    $sqlUpdateRes = "update wpky_hb_resa set " . $fieldName . " =  '" . $newValue . "' where id = " . $reservationId;

    if (strcasecmp($_POST["field"], "paid") == 0) {

        if (strcasecmp($_POST["field"], "paid") == 0) {
            if (intval($newValue) < 1) {
                $sqlUpdateRes = "update wpky_hb_resa set status = 'pending',  " . $fieldName . " =  '" . $newValue . "' where id = " . $reservationId;
            } else {
                $sqlUpdateRes = "update wpky_hb_resa set status = 'confirmed',  " . $fieldName . " =  '" . $newValue . "' where id = " . $reservationId;
            }
        } else {
            $sqlUpdateRes = "update wpky_hb_resa set status = 'confirmed',  " . $fieldName . " =  '" . $newValue . "' where id = " . $reservationId;
        }

        if (strcasecmp($_POST["field"], "paid") == 0) {
            $paid = getResPaid($reservationId);
            if (intval($paid) < 1) {
                if (! isDatesAvailable($reservationId)) {
                    $temparray1 = array(
                        'result_code' => 1,
                        'result_desciption' => "Selected dates not available"
                    );
                    echo json_encode($temparray1);
                    exit();
                }

                // get check in and checkout date
                $resDates = getBookingDates($reservationId);
                $accomId = getResAccomId($reservationId);
                // block connected accomodation
                $accomToBlockId = getAccomToBlock($accomId);
                if (! strcasecmp($accomToBlockId, "none") == 0) {

                    blockRoom($accomToBlockId, $resDates['check_in'], $resDates['check_out'], "Auto block - connected room booked", $reservationId);
                }
            } else {
                if (strcasecmp($newValue, "0") == 0) {
                    unBlockRoomByResId($reservationId);
                }
            }

            $resultCreateRes = updaterecord($sqlUpdateRes);
            if (strcasecmp($resultCreateRes, "Record updated successfully") == 0) {
                $temparray1 = array(
                    'result_code' => 0,
                    'result_desciption' => "Payment successfully updated"
                );
                echo json_encode($temparray1);

                sendReceipt($reservationId, $newValue);

                // update invoice on server
  
                $getReservationDetailsSql = "SELECT wpky_hb_resa.id, accom_id, paid, price, post_title, status, admin_comment, check_in, check_out, info
FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE
`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
and wpky_posts.ID = `wpky_hb_resa`.accom_id
and wpky_hb_resa.id = " . $reservationId;

                $result = querydatabase($getReservationDetailsSql);

                $rsType = gettype($result);

                
                if (strcasecmp($rsType, "string") == 0) {
                    exit();
                } else {
                    while ($results = $result->fetch_assoc()) {
                        $jsonObj = json_decode($results["info"]);
                        
                        $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;
                        $customerPhone = $jsonObj->phone;
                       
                        
                        $checkin_date = strtotime($results["check_in"]);
                        $checkout_date = strtotime($results["check_out"]);
                        
                        $datediff = $checkout_date - $checkin_date;
                        $resaNights = round($datediff / (60 * 60 * 24));
                        $pricePerrNight = getRoomPriceByID($results["accom_id"]);
                        
                        
                    
                        createInvoicePDF($guestName, $customerPhone, $reservationId, $results["check_in"], $results["check_out"], $pricePerrNight['price'], $pricePerrNight['price'], $resaNights, $results["post_title"], $newValue);
                    }
                }
                
                
            } else {
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to update payment"
                );
                echo json_encode($temparray1);
            }
        }
    } else {
        $resultCreateRes = updaterecord($sqlUpdateRes);
        if (strcasecmp($resultCreateRes, "Record updated successfully") == 0) {
            
            if (strcasecmp($_POST["new_value"], "cancelled") == 0) {
                unBlockRoomByResId($_POST["reservation_id"]);
            }
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => $_POST["field"] . " field successfully updated"
            );
            echo json_encode($temparray1);
            // sendReceipt($reservationId, $newValue);
        } else {
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => $_POST["field"] . " field failed to update"
            );
            echo json_encode($temparray1);
        }
    }
}

function sendReceipt($reservationId, $newValue)
{
    
    $sqlPrice = "select price, email,info  from wpky_hb_resa,  `wpky_hb_customers`
where `wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
and  wpky_hb_resa.id = " . $reservationId;

    // echo $sqlPrice;
    $result = querydatabase($sqlPrice);
    $price = "";
    $guestName = "";

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Failed to send receipt"
        );
        echo json_encode($temparray1);
        exit();
    } else {

        while ($results = $result->fetch_assoc()) {
            $jsonObj = json_decode($results["info"]);
            $price = $results["price"];
            $customerPhone = $jsonObj->phone;
            $guestName = $jsonObj->first_name;
        }
    }
    
    $outstanding = intval($price) - intval($newValue);
    sendSMS($guestName, $reservationId, $newValue, $outstanding,$customerPhone);
    
}

function sendSMS( $guestName, $resaId, $paid, $outstanding,$customerPhone)
{
    try {

        $messageBody = "Hi " . $guestName . ", Thank you for payment, Your Booking is confirmed. Balance is R" . $outstanding . ". View your reciept http://aluvegh.co.za/invoices/" . $resaId . ".pdf";
        //echo $messageBody;
        
        $formatedCustomerNumber = $customerPhone;

         if (strpos($formatedCustomerNumber, '+27') == false) {
            $formatedCustomerNumber = '+27' . $customerPhone;
        }
        
        $messages = array(
            array("from"=>COMPANY_PHONE_NUMBER,"to"=>$formatedCustomerNumber, "body"=>$messageBody)
        );
        
        if (strcasecmp($_SERVER['SERVER_NAME'], "localhost") == 0) {
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

