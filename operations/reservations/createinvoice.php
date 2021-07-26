<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/commons.php');
require_once (__DIR__ . '/../utils/invoice.php');
require_once (__DIR__ . '/blockroom.php');
require_once (__DIR__ . '/../utils/sms.php');

if (isset($_POST["action"])) {
    if (strcasecmp($_POST["action"], "create") == 0) {
        createInvoice();
    } else {
        updateInvoice($_POST["checkin_date"], $_POST["checkout_date"], $_POST["accom_id"], $_POST["total_due"], $_POST["res_notes"], $_POST["action"], 0);
    }
} else {
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide required data"
    );
    echo serialize($temparray1);
}

function createInvoice()
{
    $return_array = array();
    $now = new DateTime();

    if (! isDatesAvailableWithDates($_POST["checkin_date"], $_POST["checkout_date"], $_POST["accom_id"])) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Selected dates not available"
        );
        echo json_encode($temparray1);
        exit();
    }

    $customer_id = createCustomer($_POST["userName"], $_POST["userNumber"]);

    if (strcasecmp($customer_id, "failed to create customer") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Failed to create invoice"
        );
        echo json_encode($temparray1);
        exit();
    } else {
        // check if user selected to block the room
        $status = "pending";
        $paid = "0";
        if (isset($_POST["paid"])) {
            if (strlen($_POST["paid"]) > 0) {
                $status = "confirmed";
                $paid = $_POST["paid"];
            }
        }

        $depositDue = intval($_POST["total_due"]) / 2;

        
        $sqlCreateinvoice = "INSERT INTO `wpky_hb_resa` (`check_in`, `check_out`, `accom_id`, `accom_num`, `adults`, `children`, `price`, `deposit`, `paid`, `payment_gateway`, `currency`, `customer_id`, `status`, `options`, `additional_info`, `payment_type`, `payment_info`, `admin_comment`, `lang`, `coupon`, `payment_token`, `payment_status`, `payment_status_reason`, `amount_to_pay`, `received_on`, `updated_on`, `uid`, `origin`, `synchro_id`, `booking_form_num`, `accom_price`, `discount`, `previous_price`, `fees`, `coupon_value`, `origin_url`)
VALUES
('" . $_POST["checkin_date"] . "', '" . $_POST["checkout_date"] . "', " . $_POST["accom_id"] . ", 1, 1, 0, '" . $_POST["total_due"] . "', '" . $depositDue . "', '" . $paid . "', '', 'ZAR', " . $customer_id . ", '" . $status . "', '[]', '[]', '', '', '', 'en_US', '', '', '', '', '0', '" . $now->format('Y-m-d H:i:s') . "', '" . $now->format('Y-m-d H:i:s') . "', '" . uniqid() . "@http://aluvegh.co.za', 'website', '', 0, '" . $_POST["total_due"] . "', '', '0.00', '', '0.00', '')";

        $resultCreateRes = insertrecord($sqlCreateinvoice);
        if (strcasecmp($resultCreateRes, "New record created successfully") == 0) {

            $sqlInvoiceID = "select wpky_hb_resa.id , post_title from wpky_hb_resa, wpky_posts
where  wpky_posts.ID = `wpky_hb_resa`.accom_id
 order by received_on desc limit 1";
            $result = querydatabase($sqlInvoiceID);
            $newInvoiceID = "";
            $rooName = "";

            $rsType = gettype($result);

            if (strcasecmp($rsType, "string") == 0) {
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to create invoice"
                );
                echo json_encode($temparray1);
                exit();
            } else {

                while ($results = $result->fetch_assoc()) {
                    $newInvoiceID = $results["id"];
                    $rooName = $results["post_title"];
                }
            }
            
            $paid = "0";
            if (isset($_POST["paid"]) && !empty($_POST["paid"]) ) {
                $paid = $_POST["paid"];
            }
                
         
            if (! sendSMS( $_POST["userName"], $_POST["userNumber"], $newInvoiceID, $_POST["checkin_date"], $_POST["checkout_date"], $_POST["price_per_night"], $_POST["total_due"], $_POST["number_of_night"], $rooName,$paid)) {
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to send SMS"
                );
                echo json_encode($temparray1);
            } else {

                if (strcasecmp($status, "confirmed") == 0) {
                    $accomToBlockId = getAccomToBlock($_POST["accom_id"]);
                    if (! strcasecmp($accomToBlockId, "none") == 0) {

                        blockRoom($accomToBlockId, $_POST["checkin_date"], $_POST["checkout_date"], "Auto block - connected room booked", $newInvoiceID);
                    }
                }

                $temparray1 = array(
                    'result_code' => 0,
                    'result_desciption' => "Invoice successfully created"
                );

                echo json_encode($temparray1);
            }
        } else {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => $resultCreateRes
            );
            echo json_encode($temparray1);
        }
    }
}

function updateInvoice($checkin_date, $checkout_date, $accom_id, $total_due, $res_notes, $resID, $amountPaid)
{
    $return_array = array();
    $now = new DateTime();

    if (! isDatesAvailableWithDatesAndRes($_POST["checkin_date"], $_POST["checkout_date"], $_POST["accom_id"], $_POST["action"])) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Selected dates not available"
        );
        echo json_encode($temparray1);
        exit();
    }

    $sqlUpdateInvoice = "UPDATE `wpky_hb_resa` SET `check_in` = '" . $_POST["checkin_date"] . "', `check_out` = '" . $_POST["checkout_date"] . "', `accom_id` = " . $_POST["accom_id"] . ", `price` = " . $_POST["total_due"] . ", `admin_comment` = '" . $_POST["res_notes"] . "', `updated_on` = '" . $now->format('Y-m-d H:i:s') . "', `accom_price` = " . $_POST["total_due"] . "  WHERE `id` = " . $_POST["action"] . ";";

    
    
    $resultCreateRes = insertrecord($sqlUpdateInvoice);
    if (strcasecmp($resultCreateRes, "New record created successfully") == 0) {
        
        
        $sqlInvoiceID = "select wpky_hb_resa.id , post_title, customer_id from wpky_hb_resa, wpky_posts
where  wpky_posts.ID = `wpky_hb_resa`.accom_id
and  wpky_hb_resa.id = ".$resID."
 order by updated_on desc limit 1";

        $result = querydatabase($sqlInvoiceID);
        $newInvoiceID = "";
        $rooName = "";
        $customer_id = "";
        
        $rsType = gettype($result);
        
        if (strcasecmp($rsType, "string") == 0) {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Failed to update invoice"
            );
            echo json_encode($temparray1);
            exit();
        } else {

            while ($results = $result->fetch_assoc()) {
                $newInvoiceID = $results["id"];
                $rooName = $results["post_title"];
                $customer_id = $results["customer_id"];
            }
            
            if(!updateCustomer($customer_id, $_POST["userName"], $_POST["userNumber"])){
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to update customer details"
                );
                echo json_encode($temparray1);
                exit();
            }
            
        }
        
        if (! sendSMS( $_POST["userName"], $_POST["userNumber"], $newInvoiceID, $_POST["checkin_date"], $_POST["checkout_date"], $_POST["price_per_night"], $_POST["total_due"], $_POST["number_of_night"], $rooName, $amountPaid)) {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Failed to send SMS"
            );
            echo json_encode($temparray1);
        } else {
            
            unBlockRoomByResId($newInvoiceID);
            $accomToBlockId = getAccomToBlock($_POST["accom_id"]);

            if (! strcasecmp($accomToBlockId, "none") == 0) {
                
                blockRoom($accomToBlockId, $_POST["checkin_date"], $_POST["checkout_date"], "Auto block - connected room booked", $newInvoiceID);
            }
            
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => "Invoice successfully updated"
            );
            echo json_encode($temparray1);
        }
    } else {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => $resultCreateRes
        );
        echo json_encode($temparray1);
    }
}


function updateCustomer($customerID, $customerName, $phone)
{
    $sqlUpdateCustomer = "Update  `wpky_hb_customers`
 SET `email` = 'noemail@gmail.com', info =  '{\"first_name\":\"" . $customerName . "\",\"last_name\":\"\",\"email\":\"noemail@gmail.com\",\"phone\":\"" . $phone . "\"}'
where id = " . $customerID . ";";
    
    //echo $sqlUpdateCustomer;
    $resultCustomer = updaterecord($sqlUpdateCustomer);
    
    if (strcasecmp($resultCustomer, "Record updated successfully") == 0) {
        return true;
    } else {
        return false;
    }
}


function sendSMS( $guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName, $paid)
{
    try {

        
        if (! createInvoicePDF($guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName, $paid)) {
            return false;
        }
        
        $header = "invoice";
        if (strcmp($paid, "0") !== 0) {
            $header = "booking";
        }
        
        $messageBody = "";
        
        if (strcasecmp($_POST["action"], "create") == 0) {
            $messageBody = "Hi " . $guestName . ", Your " . $header . " is ready. Please make payment to confirm reservation. Click to view http://aluvegh.co.za/invoices/" . $resID . ".pdf";
        } else {
            $messageBody = "Hi " . $guestName . ", Your " . $header . " was updated. Click to view http://aluvegh.co.za/invoices/" . $resID . ".pdf";
        }
        
        $formatedCustomerNumber = $customerPhone;
        if (strpos($customerPhone, '+27') == false) {
            $formatedCustomerNumber = '+27' . substr($customerPhone, 1);
            $formatedCustomerNumber = str_replace("+270", "+27", $formatedCustomerNumber);
        }
        
        echo $formatedCustomerNumber;
        $messages = array(
            array("from"=>"+27796347610","to"=>$formatedCustomerNumber, "body"=>$messageBody)
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
        echo $e;
        return false;
    }
}

function createCustomer($customerName, $phone)
{
    $sqlCreateCustomer = "INSERT INTO `wpky_hb_customers`
(
`email`,
`info`,
`payment_id`)
VALUES
(
'noemail@gmail.com',
'{\"first_name\":\"" . $customerName . "\",\"last_name\":\"\",\"email\":\"noemail@gmail.com\",\"phone\":\"" . $phone . "\"}',
'');";

    $sqlCheckCustomerExists = "select id from wpky_hb_customers where info LIKE '%" . $phone . "%'";

    $resultCustomer = querydatabase($sqlCheckCustomerExists);
    $rsType = gettype($resultCustomer);

    if (strcasecmp($rsType, "string") == 0) {
        $resultCreateCustomer = insertrecord($sqlCreateCustomer);
        if (strcasecmp($resultCreateCustomer, "New record created successfully") == 0) {
            $resultCustomer = querydatabase($sqlCheckCustomerExists);
            $rsType = gettype($resultCustomer);

            if (strcasecmp($rsType, "string") !== 0) {
                while ($results = $resultCustomer->fetch_assoc()) {
                    return $results["id"];
                }
            } else {
                return "failed to create customer";
            }
        } else {
            return "failed to create customer";
        }
    } else {
        while ($results = $resultCustomer->fetch_assoc()) {
            return $results["id"];
        }
    }
}
