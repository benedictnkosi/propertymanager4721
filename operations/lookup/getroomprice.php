<?php
require_once (__DIR__ . '/../utils/data.php');

if (isset($_GET["accom_id"])) {
   
    echo json_encode(getRoomPriceByID($_GET["accom_id"]));
   
} else {
    /*$temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide room ID"
    );
    echo serialize(json_encode($temparray1));*/
}

function getRoomPriceByID($accomId)
{

    $sql = "SELECT pricing FROM renugtaj_wp163.wpky_hotel_booking_plans 
where room_id = (select meta_value from wpky_postmeta
where post_id = " . $accomId . "
and meta_key = 'accom_linked_page');";

    $temparray1 = Array();
    $result = querydatabase($sql);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "No prices found"
        );
        return $temparray1;
        exit();
    } else {

        while ($results = $result->fetch_assoc()) {
            // echo $results["pricing"];

            $arrayPrices = explode(":\"", $results["pricing"]);
            $price = 0;
            foreach ($arrayPrices as &$price) {
                if (strpos($price, "\"") !== false) {
                    $price = substr($price,0,strpos($price,"\""));
                }
            }


            $temparray1 = array(
                'price' => $price,
                'result_code' => 0,
                'result_desciption' => "success"
            );
        }
        
        return $temparray1;
    }
}