<?php

require_once (__DIR__ . '/../utils/data.php');



if (isset($_POST["userName"])

    && isset($_POST["userEmail"])

    && isset($_POST["userNumber"])

    && isset($_POST["total_due"])

    && isset($_POST["checkin_date"])

    && isset($_POST["checkout_date"])

    && isset($_POST["accom_id"])) {

        createReservation();

    }else {

        $temparray1 = array(

            'result_code' => 1,

            'result_desciption' => "Please provide required data"

        );

        echo serialize($temparray1);

    }

    

    

function createReservation()

{

    $return_array = array();

    $now = new DateTime();



    $customer_id = createCustomer($_POST["userName"], $_POST["userEmail"], $_POST["userNumber"]);



    if (strcasecmp($customer_id, "failed to create customer") == 0) {

        $temparray1 = array(

            'result_code' => 1,

            'result_desciption' => "Failed to create reservation"

        );

        echo json_encode($temparray1);

        exit();

    } else {

        //check if user selected to block the room

        $status = "pending";

        if(isset($_POST["block_dates"])){

            $status = "confirmed";

        }

        

        $sqlCreateRes = "INSERT INTO `wpky_hb_resa` (`check_in`, `check_out`, `accom_id`, `accom_num`, `adults`, `children`, `price`, `deposit`, `paid`, `payment_gateway`, `currency`, `customer_id`, `status`, `options`, `additional_info`, `payment_type`, `payment_info`, `admin_comment`, `lang`, `coupon`, `payment_token`, `payment_status`, `payment_status_reason`, `amount_to_pay`, `received_on`, `updated_on`, `uid`, `origin`, `synchro_id`, `booking_form_num`, `accom_price`, `discount`, `previous_price`, `fees`, `coupon_value`, `origin_url`)

VALUES

('" . $_POST["checkin_date"] . "', '" . $_POST["checkout_date"] . "', " . $_POST["accom_id"] . ", 1, 1, 0, '0', '0', '0', '', 'ZAR', $customer_id, '".$status."', '[]', '[]', '', '', '', 'en_US', '', '', '', '', '0', '" . $now->format('Y-m-d H:i:s') . "', '" . $now->format('Y-m-d H:i:s') . "', '" . uniqid() . "@http://aluvegh.co.za', 'website', '', 0, '-1.00', '', '0.00', '', '0.00', '')";



        $resultCreateRes = insertrecord($sqlCreateRes);

        if (strcasecmp($resultCreateRes, "New record created successfully") == 0) {

            $temparray1 = array(

                'result_code' => 0,

                'result_desciption' => "Reservation successfully created"

            );

            echo json_encode($temparray1);

        } else {

            $temparray1 = array(

                'result_code' => 1,

                'result_desciption' => $resultCreateRes

            );

            echo json_encode($temparray1);

        }

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

'".$email."',

'{\"first_name\":\"" . $customerName . "\",\"last_name\":\"\",\"email\":\"$email\",\"phone\":\"" . $phone . "\"}',

'');";



    $sqlCheckCustomerExists = "select id from wpky_hb_customers where email = '" . $email . "'";



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







