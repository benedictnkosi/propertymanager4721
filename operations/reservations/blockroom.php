<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/commons.php');

if (isset($_POST["accom_id"]) && isset($_POST["checkin_date"]) && isset($_POST["block_notes"]) && isset($_POST["checkout_date"])) {
    echo json_encode(blockRoom($_POST["accom_id"] ,$_POST["checkin_date"],$_POST["checkout_date"], $_POST["block_notes"], 0));
} else if (isset($_POST["block_id"]) ) {
    echo json_encode(unBlockRoom($_POST["block_id"]));
}else {
    if (! isset($_POST["field"])) {
       /* $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Please provide required data"
        );
        echo json_encode($temparray1);*/
    }
}

function blockRoom($accom_id, $checkin_date, $checkout_date, $block_notes, $resID)
{
    
    $return_array = array();
    $now = new DateTime();

    if (! isDatesAvailableWithDates($checkin_date, $checkout_date, $accom_id)) {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Selected dates not available"
        );
        
        return $temparray1;
        exit();
    }
   // echo "test";
    
    $sqlBlockRoom = "INSERT INTO `wpky_hb_accom_blocked` (`id`, `accom_id`, `accom_all_ids`, `accom_num`, `accom_all_num`, `from_date`, `to_date`, `linked_resa_id`, `comment` , `uid`, `is_prepa_time`) 
VALUES (NULL, '" . $accom_id . "', '0', '1', '0', '" . $checkin_date . "', '" . $checkout_date . "', ".$resID." , '" . $block_notes . "', 'D2020-08-07T16:56:15U5f2d587afe9621@http://renuga.co.za', '0');";

    //echo $sqlBlockRoom;
    $resultBlock = insertrecord($sqlBlockRoom);
    if (strcasecmp($resultBlock, "New record created successfully") == 0) {

        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Room successfully blocked"
        );
        return $temparray1;
    } else {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Failed to block room"
        );
        return $temparray1;
    }
}

function unBlockRoom($blockId)
{
    $sqlBlockRoom = "delete from wpky_hb_accom_blocked where id = " . $blockId;
    //echo     $sqlBlockRoom;
    $resultBlock = deleterecord($sqlBlockRoom);
    if (strcasecmp($resultBlock, "Record deleted successfully") == 0) {

        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Room successfully unblocked"
        );
        return $temparray1;
    } else {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Failed to unblock room"
        );
        return $temparray1;
    }
}


function unBlockRoomByResId($resId)
{
    $sqlBlockRoom = "delete from wpky_hb_accom_blocked where linked_resa_id = " . $resId;
    //echo     $sqlBlockRoom;
    $resultBlock = deleterecord($sqlBlockRoom);
    if (strcasecmp($resultBlock, "Record deleted successfully") == 0) {
        
        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Room successfully unblocked"
        );
        return $temparray1;
    } else {
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Failed to unblock room"
        );
        return $temparray1;
    }
}

