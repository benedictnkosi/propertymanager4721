<?php

require_once (__DIR__ . '/../utils/data.php');

if (isset($_POST["customer_id"])) {
    blockCustomer($_POST["customer_id"]);
}

function blockCustomer($customerId)
{
   $return_array = array();

    $sqlUpdateRes = "update wpky_hb_customers set state =  'blocked', comments = ' " . $_POST["comments"] ."' where id = " . $customerId;
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
