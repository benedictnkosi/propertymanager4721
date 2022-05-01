<?php

require_once (__DIR__ . '/../utils/data.php');

if (isset($_POST["customer_id"])) {
    blockCustomer($_POST["customer_id"]);
}

function blockCustomer($customerId)
{
   $return_array = array();

    $sqlUpdateRes = "update wpky_hb_customers set state =  'blocked', comments = ' " . $comments .'" where id = " . $customerId;
}
