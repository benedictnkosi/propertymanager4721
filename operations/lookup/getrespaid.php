<?php

function getResPaid($resID)
{
    $sql = "SELECT paid from wpky_hb_resa where id = " . $resID;

    $result = querydatabase($sql);

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        return "";
    } else {

        while ($results = $result->fetch_assoc()) {
            return $results["paid"];
        }
        echo json_encode($temparray1);
    }
}