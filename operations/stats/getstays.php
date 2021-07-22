<?php
require_once (__DIR__ . '/../utils/data.php');

function getNumberOfStays($customerID){
    $numberOfStays = 0;
    $numberOfStaysSQL = "SELECT count(*) as stays FROM `wpky_hb_resa` WHERE `customer_id` = " . $customerID;
    $result = querydatabase($numberOfStaysSQL);
    $rsType = gettype($result);
    
    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {
            $numberOfStays = $results["stays"];
        }
        
    }
    
    return $numberOfStays;
}
