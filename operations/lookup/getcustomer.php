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


function getLastRooms($customerId){
    $sql = "SELECT wpky_hb_resa.id, wpky_hb_customers.id as customer_id, accom_id, post_title, check_in
        
FROM `wpky_hb_resa`, `wpky_hb_customers`, wpky_posts WHERE
        
`wpky_hb_resa`.`customer_id` = `wpky_hb_customers`.`id`
        
and wpky_posts.ID = `wpky_hb_resa`.accom_id
        
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))
        
and wpky_hb_customers.id = ".$customerId."
and DATE(check_in) < DATE(NOW())
group by post_title 
order by check_in desc";
    
    $result = querydatabase($sql);
    $rsType = gettype($result);
    $rooms = "";
    if (strcasecmp($rsType, "string") == 0) {
        return "";
    }else{
        while ($results = $result->fetch_assoc()) {
            $rooms = $rooms . $results["post_title"] . ", ";
        }
    }
    
    return $rooms;
}

function getCustomerByNumber(){
    $sql = "SELECT id, info, email, id_image, state, comments FROM wpky_hb_customers where info LIKE '%".$_GET["phone_number"]."%';";
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
            $previousRooms = getLastRooms($results["id"]);
            $state = json_decode($results["state"]);
            $comments = json_decode($results["comments"]);
            $jsonObj = json_decode($results["info"]);
            
            $guestName = $jsonObj->first_name . ' ' . $jsonObj->last_name;

            
            $temparray1 = array(
                'guest_name' => $guestName,
                'guest_email' => $results["email"],
                'image' => $results["id_image"],
                'rooms' => $previousRooms,
                'status' => $state,
                'comments' => $comments,
                'result_code' => 0,
                'result_desciption' => "success"
            );
        }
        echo json_encode($temparray1);
    }
}
