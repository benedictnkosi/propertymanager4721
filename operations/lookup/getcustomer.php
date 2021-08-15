<?php

require_once (__DIR__ . '/../utils/data.php');

if(isset($_GET["phone_number"])){
    getCustomerByNumber();
}else{
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide phone number"
    );
    echo serialize($temparray1);
}


function getCustomerByNumber(){
    $sql = "SELECT info, email, id_image FROM wpky_hb_customers where info LIKE '%".$_GET["phone_number"]."%';";
    $temparray1 = Array();
    $result = querydatabase($sql);
    
    $rsType = gettype($result);
    
    
    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "No results found"
        );
        echo serialize($temparray1);
        exit();
    } else {
        
        while ($results = $result->fetch_assoc()) {
            $guestName = "";
            $guestEmail = "";
            $jsonObj = json_decode($results["info"]);
            
            $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;

            
            $temparray1 = array(
                'guest_name' => $guestName,
                'guest_email' => $results["email"],
                'image' => $results["id_image"],
                'result_code' => 0,
                'result_desciption' => "success"
            );
        }
        echo json_encode($temparray1);
    }
    
    
    
}