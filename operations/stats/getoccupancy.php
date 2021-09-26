<?php
require_once (__DIR__ . '/../utils/data.php');
$temparray1;

if (isset($_GET["days"])) {
    if (isset($_GET["type"])) {
        if (strcasecmp($_GET["type"], "room") == 0) {
            getOccupancyPerRoom($_GET["days"]);
        } else if (strcasecmp($_GET["type"], "overall") == 0) {
            getOverallOccupancy($_GET["days"]);
        } else {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Please provide room or overall for type"
            );
            echo json_encode($temparray1);
        }
    }
} else {
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide days"
    );
    echo json_encode($temparray1);
}

function getOccupancyPerRoom($days)
{
    $return_array = array();

    if (! is_numeric($days)) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Please provide a number for days"
        );
        echo json_encode($temparray1);
        exit();
    }
    
    $sql = "SELECT accom_id, post_title, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM `wpky_hb_resa`, wpky_posts
WHERE wpky_posts.ID = `wpky_hb_resa`.accom_id

and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))
and admin_comment not like '%Not available%'
group by accom_id
order by occupancy;";
    
    echo $sql;

    $result = querydatabase($sql);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        echo 'No results found';
        exit();
    } else {
        
        $familyUnitOccupancy = 0;
        $oneBedroomOccupancy = 0;
        $upstairsFamilyOccupancy = 0;
        $upstairsRoomOccupancy = 0;
        $roomName = "";
        $occupancy = 0;
        
        while ($results = $result->fetch_assoc()) {
            
            if (strcasecmp($results["post_title"], "Luxury 2 Bedroom Family Cottage") == 0) {
                if($oneBedroomOccupancy == 0){
                    $familyUnitOccupancy = round($results["occupancy"]);
                }else{
                    $roomName = "Family Unit";
                    $occupancy = round($results["occupancy"]) + $oneBedroomOccupancy;
                }
            }elseif (strcasecmp($results["post_title"], "1 Bedroom Cottage") == 0) {
                if($familyUnitOccupancy == 0){
                    $oneBedroomOccupancy = round($results["occupancy"]);
                }else{
                    $roomName = "Family Unit";
                    $occupancy = round($results["occupancy"]) + $familyUnitOccupancy;
                }
            }elseif (strcasecmp($results["post_title"], "Upstairs 4 Sleeper Family Suite Near Maboneng") == 0) {
                if($upstairsRoomOccupancy == 0){
                    $upstairsFamilyOccupancy = round($results["occupancy"]);
                }else{
                    $roomName = "Upstars Unit";
                    $occupancy = round($results["occupancy"]) + $upstairsRoomOccupancy;
                }
            }elseif (strcasecmp($results["post_title"], "Beautiful Upstairs Room") == 0) {
                if($upstairsFamilyOccupancy == 0){
                    $upstairsRoomOccupancy = round($results["occupancy"]);
                }else{
                    $roomName = "Upstars Unit";
                    $occupancy = round($results["occupancy"]) + $upstairsFamilyOccupancy;
                }
            }else{
                $roomName = $results["post_title"];
                $occupancy = round($results["occupancy"]);
            }
            
            if (strcasecmp($roomName, "") !== 0) {
                echo '<h6>
								' .substr($roomName, 0,20) .' <span> '.$occupancy.'% </span>
							</h6>
							<div class="progress">
								<div class="progress-bar progress-bar-striped active"
									style="width: '.round($occupancy).'%"></div>
							</div>';
            }
            
            $roomName = "";
            $occupancy = 0;
            
        }
    }

}

function getOverallOccupancy($days)
{
    
    $return_array = array();
    $sql = "SELECT accom_id, post_title, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM `wpky_hb_resa`, wpky_posts
WHERE wpky_posts.ID = `wpky_hb_resa`.accom_id

and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and (`status` = 'confirmed' or (`status` = 'pending' and paid NOT IN ('0.00')) or (`status` = 'pending' and origin NOT IN ('website')))
and admin_comment not like '%Not available%'
group by accom_id
order by occupancy;";

    //echo $sql;
    $result = querydatabase($sql);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "No results found"
        );
        echo json_encode($temparray1);
        exit();
    } else {
        $familyUnitOccupancy = 0;
        $oneBedroomOccupancy = 0;
        $upstairsFamilyOccupancy = 0;
        $upstairsRoomOccupancy = 0;
        
        $numberOfUnits = 0;
        $sum = 0;

        while ($results = $result->fetch_assoc()) {
            
            if (strcasecmp($results["post_title"], "Luxury 2 Bedroom Family Cottage") == 0) {
                if($oneBedroomOccupancy == 0){
                    $familyUnitOccupancy = round($results["occupancy"]);
                }else{
                   
                    $numberOfUnits ++;
                    $sum += round($results["occupancy"]) + $oneBedroomOccupancy;
                    
                }
            }elseif (strcasecmp($results["post_title"], "1 Bedroom Cottage") == 0) {
                if($familyUnitOccupancy == 0){
                    $oneBedroomOccupancy = round($results["occupancy"]);
                }else{
                 
                    $numberOfUnits ++;
                    $sum += round($results["occupancy"]) + $familyUnitOccupancy;
                    
                }
            }elseif (strcasecmp($results["post_title"], "Upstairs 4 Sleeper Family Suite Near Maboneng") == 0) {
                if($upstairsRoomOccupancy == 0){
                    $upstairsFamilyOccupancy = round($results["occupancy"]);
                }else{
                   
                    $numberOfUnits ++;
                    $sum += round($results["occupancy"]) + $upstairsRoomOccupancy;
                    
                    
                }
            }elseif (strcasecmp($results["post_title"], "Beautiful Upstairs Room") == 0) {
                if($upstairsFamilyOccupancy == 0){
                    $upstairsRoomOccupancy = round($results["occupancy"]);
                }else{
 
                    $numberOfUnits ++;
                    $sum += round($results["occupancy"]) + $upstairsFamilyOccupancy;
                    
                }
            }else{
                $numberOfUnits ++;
                $sum += $results["occupancy"];
            }
            
            
        }

        $avg = $sum / $numberOfUnits;
        $temparray1 = array(
            'occupancy' => round($avg) . '%',
            'result_code' => 0,
            'result_desciption' => "success"
        );
        echo json_encode($temparray1);
    }
}
