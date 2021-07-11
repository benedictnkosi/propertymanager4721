<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/email_template.php');
require_once (__DIR__ . "/../utils/mail.php");
require_once (__DIR__ . '/commons.php');
require_once (__DIR__ . '/../utils/invoice.php');
require_once (__DIR__ . '/blockroom.php');

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

    $customer_id = createCustomer($_POST["userName"], $_POST["userEmail"], $_POST["userNumber"]);

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
        if (isset($_POST["paid"])) {
            if (strlen($_POST["paid"]) > 0) {
                $status = "confirmed";
            }
        }

        $depositDue = intval($_POST["total_due"]) / 2;

        $sqlCreateinvoice = "INSERT INTO `wpky_hb_resa` (`check_in`, `check_out`, `accom_id`, `accom_num`, `adults`, `children`, `price`, `deposit`, `paid`, `payment_gateway`, `currency`, `customer_id`, `status`, `options`, `additional_info`, `payment_type`, `payment_info`, `admin_comment`, `lang`, `coupon`, `payment_token`, `payment_status`, `payment_status_reason`, `amount_to_pay`, `received_on`, `updated_on`, `uid`, `origin`, `synchro_id`, `booking_form_num`, `accom_price`, `discount`, `previous_price`, `fees`, `coupon_value`, `origin_url`)
VALUES
('" . $_POST["checkin_date"] . "', '" . $_POST["checkout_date"] . "', " . $_POST["accom_id"] . ", 1, 1, 0, '" . $_POST["total_due"] . "', '" . $depositDue . "', '" . $_POST["paid"] . "', '', 'ZAR', " . $customer_id . ", '" . $status . "', '[]', '[]', '', '', '', 'en_US', '', '', '', '', '0', '" . $now->format('Y-m-d H:i:s') . "', '" . $now->format('Y-m-d H:i:s') . "', '" . uniqid() . "@http://aluvegh.co.za', 'website', '', 0, '" . $_POST["total_due"] . "', '', '0.00', '', '0.00', '')";

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
            if (! sendEmail($_POST["userEmail"], $_POST["userName"], $_POST["userNumber"], $newInvoiceID, $_POST["checkin_date"], $_POST["checkout_date"], $_POST["price_per_night"], $_POST["total_due"], $_POST["number_of_night"], $rooName)) {
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to email invoice"
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
            
            if(!updateCustomer($customer_id, $_POST["userName"], $_POST["userNumber"], $_POST["userEmail"])){
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Failed to update customer details"
                );
                echo json_encode($temparray1);
                exit();
            }
            
        }
        
        if (! sendEmail($_POST["userEmail"], $_POST["userName"], $_POST["userNumber"], $newInvoiceID, $_POST["checkin_date"], $_POST["checkout_date"], $_POST["price_per_night"], $_POST["total_due"], $_POST["number_of_night"], $rooName, $amountPaid)) {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Failed to email invoice"
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


function updateCustomer($customerID, $customerName, $email, $phone)
{
    $sqlUpdateCustomer = "Update  `wpky_hb_customers`
 SET `email` = '" . $email . "', info =  '{\"first_name\":\"" . $customerName . "\",\"last_name\":\"\",\"email\":\"$email\",\"phone\":\"" . $phone . "\"}'
where id = " . $customerID . ";";
    
    //echo $sqlUpdateCustomer;
    $resultCustomer = updaterecord($sqlUpdateCustomer);
    
    if (strcasecmp($resultCustomer, "Record updated successfully") == 0) {
        return true;
    } else {
        return false;
    }
}


function sendEmail($to, $guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName)
{
    try {

        if (! createInvoicePDF($to, $guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName, 0)) {
            return false;
        }

        $invoiceDate = new DateTime();

        $Parameters = array(
            "customer_name" => $guestName,
            "resa_check_in" => $checkin,
            "resa_check_out" => $checkout,
            "resa_accommodation" => $rooName,
            "resa_total" => "R" . number_format($total, 2),
            "resa_id" => $resID,
            "ivoice_date" => $invoiceDate->format('Y-m-d'),
            "customer_email" => $to,
            "customer_phone" => $customerPhone,
            "resa_price" => "R" . number_format($price, 2),
            "resa_nights" => $resaNights,
            "pdf_download_path" => "http://aluvegh.co.za/invoices/" . $resID . ".pdf",
            "template" => '{"quantity_header":"Nights"}'
        );

        $body = generate_email_body("invoice", $Parameters);

        $body = wordwrap($body, 70);

        // echo $body;
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . "info@aluvegh.co.za" . "\r\n";
        $headers .= 'Reply-To: ' . "info@aluvegh.co.za" . "\r\n";

        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

        if (strcasecmp($_SERVER['SERVER_NAME'], "localhost") == 0) {
            return true;
        } else {
            if (mail($to, "Aluve Guesthouse Invoice ", $body, $headers)) {
                return true;
            } else {
                return false;
            }
        }
    } catch (Exception $e) {
        return false;
    }
}

function createCustomer($customerName, $email, $phone)
{
    $sqlCreateCustomer = "INSERT INTO `wpky_hb_customers`
(
`email`,
`info`,
`payment_id`)
VALUES
(
'" . $email . "',
'{\"first_name\":\"" . $customerName . "\",\"last_name\":\"\",\"email\":\"$email\",\"phone\":\"" . $phone . "\"}',
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
