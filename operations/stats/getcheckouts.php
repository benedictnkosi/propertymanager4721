<?php
require_once (__DIR__ . '/../utils/data.php');
$temparray1;


if(isset($_GET["period"])){
    getcheckots();
}else{
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide period"
    );
    echo json_encode($temparray1);
}


function getcheckots() {
    
    $sql_todays_checkouts = "SELECT count(id) as count FROM `wpky_hb_resa` WHERE
 (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')))
	and DATE(check_out) = DATE(NOW())
	and admin_comment not like '%Not available%'";
    
    $sql_tomorrows_checkouts = "SELECT count(id) as count FROM `wpky_hb_resa` WHERE
 (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')))
	and DATE(check_out) = DATE(NOW()) + INTERVAL 1 DAY
	and admin_comment not like '%Not available%'";
    
    
    
    $checkInPeriod = $_GET["period"];
    $result;
    
    if (strcasecmp($checkInPeriod, "today") == 0) {
        $result = querydatabase($sql_todays_checkouts);
    }else if (strcasecmp($checkInPeriod, "tomorrow") == 0) {
        $result = querydatabase($sql_tomorrows_checkouts);
    }else{
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Please provide either today or tomorrow for period"
        );
        echo json_encode($temparray1);
        exit();
    }
    
    $rsType = gettype($result);
    
    
    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'count' => "0",
            'result_code' => 0,
            'result_desciption' => "success"
        );
    } else {
        while ($results = $result->fetch_assoc()) {
            $temparray1 = array(
                'count' => $results["count"],
                'result_code' => 0,
                'result_desciption' => "success"
            );
            
        }
    }
    
    echo json_encode($temparray1);
}


