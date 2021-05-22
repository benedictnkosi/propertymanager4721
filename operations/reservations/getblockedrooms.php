<?php
require_once (__DIR__ . '/../utils/data.php');

getBlockedRoomsHtml();

function getBlockedRoomsHtml()
{
    $return_array = array();

    $sql_blocked_rooms = "SELECT  wpky_hb_accom_blocked.id, wpky_hb_accom_blocked.accom_id,  post_title, from_date, to_date, comment
FROM `wpky_hb_accom_blocked`, wpky_posts WHERE
 wpky_posts.ID = `wpky_hb_accom_blocked`.accom_id
and comment not like '%Blocked automatically%'
and DATE(from_date) >= DATE(NOW())
and DATE(from_date) <= DATE(NOW()) + INTERVAL 180 DAY
and DATE(to_date) > DATE(NOW())
order by `from_date`";

    $result = querydatabase($sql_blocked_rooms);
    

    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {

        echo '<div class="res-details">
						<h4 class="guest-name">No blocked rooms</h4>
					</div>';
        exit();
    } else {

        while ($results = $result->fetch_assoc()) {

            $checkInDate = new DateTime($results["from_date"]);
            $checkOutDate = new DateTime($results["to_date"]);
            echo '<div class="res-details">
						<h4 class="guest-name">' . $results["post_title"] . ' - ' . $results["id"] . '</h4>
						
						<p name="res-dates">' . $checkInDate->format('M') . '  ' . $checkInDate->format('d') . ' - ' . $checkOutDate->format('d') . ', ' . $checkOutDate->format('Y') . '</p>
						<p name="res-dates">' . $results["comment"] . '</p>
						
';

            echo '<p class="far-right"><span class="glyphicon glyphicon-trash deleteBlockRoom clickable" aria-hidden="true" id="delete_blocked_' . $results["id"] . '"></span></p>   

                 
						<div class="clearfix">

									
					</div></div>';
        }
    }
}

