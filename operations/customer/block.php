<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../utils/sms.php');
require_once (__DIR__ . '/commons.php');
require_once (__DIR__ . '/../app/application.php');


if (isset($_POST["customer_id"])) {
    blockCustomer($_POST["customer_id"]);
}


function blockCustomer($customerId)
{
    $return_array = array();

    $sqlUpdateRes = "update wpky_hb_customers set state =  'blocked', comments = ' " . $comments .'" where id = " . $customerId;

    $resultCreateRes = updaterecord($sqlUpdateRes);
        if (strcasecmp($resultCreateRes, "Record updated successfully") == 0) {
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => "Customer successfully blocked"
            );
            echo json_encode($temparray1);
        } else {
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => "Failed to block customer"
            );
            echo json_encode($temparray1);
        }
}
