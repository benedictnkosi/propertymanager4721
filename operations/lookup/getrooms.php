<?php
require_once (__DIR__ . '/../utils/data.php');

if (isset($_GET["status"])) {
    if (isset($_GET["content-type"])) {
        if (strcasecmp($_GET["content-type"], "html") == 0) {
            getRoomsHtml($_GET["status"]);
        } else {
            getrooms($_GET["status"]);
        }
    } else {
            getrooms($_GET["status"]);
        }
    } else {
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide status"
    );
    echo serialize($temparray1);
}

function getrooms($status)
{
    $return_array = array();

    $sql = "SELECT ID, post_title, post_status FROM wpky_posts where post_type = 'hb_accommodation'
        and post_status = '" . $status . "';";

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
            $temparray1 = array(
                'accom_id' => $results["ID"],
                'room_name' => $results["post_title"],
                'status' => $results["post_status"],

                'result_code' => 0,
                'result_desciption' => "success"
            );
            array_push($return_array, $temparray1);
        }
    }

    echo serialize($return_array);
}

function getRoomsHtml($status)
{
    $return_array = array();

    $sql = "SELECT ID, post_title, post_status FROM wpky_posts where post_type = 'hb_accommodation'
        and post_status = '" . $status . "';";

    $result = querydatabase($sql);

    $rsType = gettype($result);
    echo '<option value="SelectRoom">Select Room</option>';
    if (strcasecmp($rsType, "string") !== 0) {

        while ($results = $result->fetch_assoc()) {
            echo '<option value="' . $results["ID"] . '">' . $results["post_title"] . '</option>';
        }
    }
}




