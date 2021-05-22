<?php

function isDatesAvailableWithDates($checkinDate, $checkOut, $accomodationID)
{
    $sql = 'select * from wpky_hb_resa, wpky_posts
where wpky_posts.ID = wpky_hb_resa.accom_id
and wpky_hb_resa.accom_id = ' . $accomodationID . '
and (`status` = "confirmed" or (`status` = "pending" and paid NOT IN ("0.00")) or (`status` = "pending" and origin NOT IN ("website")))
and ( (DATE(check_out) > DATE("' . $checkinDate . '") and DATE(check_in) <= DATE("' . $checkinDate . '"))
or (DATE(check_in) < DATE("' . $checkOut . '") and DATE(check_in) > DATE("' . $checkinDate . '")))
 and admin_comment not like "%Not available%" 
';
    
   // echo $sql;

    $result = querydatabase($sql);
    //print_r($result);
    $rsType = gettype($result);

    $pos = strpos($rsType, "string");

    //echo $pos;
    if ($pos === false) {
        while ($results = $result->fetch_assoc()) {
            return false;
        }
    } else {
        if (isDatesBlocked($checkinDate, $checkOut, $accomodationID)) {
            return false;
        } else {
            return true;
        }
        
    }
}


function isDatesAvailableWithDatesAndRes($checkinDate, $checkOut, $accomodationID, $resIDToExclude)
{
    $sql = 'select * from wpky_hb_resa, wpky_posts
where wpky_posts.ID = wpky_hb_resa.accom_id
and wpky_hb_resa.accom_id = ' . $accomodationID . '
and (`status` = "confirmed" or (`status` = "pending" and paid NOT IN ("0.00")) or (`status` = "pending" and origin NOT IN ("website")))
and ( (DATE(check_out) > DATE("' . $checkinDate . '") and DATE(check_in) <= DATE("' . $checkinDate . '"))
or (DATE(check_in) < DATE("' . $checkOut . '") and DATE(check_in) > DATE("' . $checkinDate . '")))
 and admin_comment not like "%Not available%"
and wpky_hb_resa.id <> $resIDToExclude
';
    
   // echo $sql;
    $result = querydatabase($sql);
    //print_r($result);
    $rsType = gettype($result);
    
    $pos = strpos($rsType, "string");
    
    //echo $pos;
    if ($pos === false) {
        while ($results = $result->fetch_assoc()) {
            return false;
        }
    } else {
        if (isDatesBlocked($checkinDate, $checkOut, $accomodationID)) {
            return false;
        } else {
            return true;
        }
        
    }
}

function isDatesAvailable($resID)
{
    $sqlGetResDates = "select * from wpky_hb_resa where id = " . $resID;
    $result = querydatabase($sqlGetResDates);
    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {
            if (isDatesAvailableWithDatesAndRes($results["check_in"], $results["check_out"], $results["accom_id"], $resID)) {
                return true;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}

function getBookingDates($resID)
{
    $sqlGetResDates = "select * from wpky_hb_resa where id = " . $resID;
    $result = querydatabase($sqlGetResDates);
    $rsType = gettype($result);
    $temparray1 = array();
    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {
            $temparray1 = array(
                'check_in' => $results['check_in'],
                'check_out' => $results['check_out']
            );
            return $temparray1;
        }
    } else {
        return $temparray1;
    }
}

function getResAccomId($resID)
{
    $sqlGetResDates = "select * from wpky_hb_resa where id = " . $resID;
    $result = querydatabase($sqlGetResDates);
    $rsType = gettype($result);
    $temparray1 = array();
    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {

            return $results['accom_id'];
        }
    } else {
        return $temparray1;
    }
}

function isDatesBlocked($checkinDate, $checkOut, $accomodationID)
{
    $sql = 'select * from wpky_hb_accom_blocked, wpky_posts
where wpky_posts.ID = wpky_hb_accom_blocked.accom_id
and wpky_hb_accom_blocked.accom_id = ' . $accomodationID . '
and ( (DATE(to_date) > DATE("' . $checkinDate . '") and DATE(from_date) <= DATE("' . $checkinDate . '"))
or (DATE(from_date) < DATE("' . $checkOut . '") and DATE(from_date) > DATE("' . $checkinDate . '")))';

    $result = querydatabase($sql);
    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {
            return true;
        }
    } else {
        return false;
    }
}

function getAccomToBlock($accomodationID)
{
    $sql = "select * from wpky_postmeta
where post_id = " . $accomodationID . "
and meta_key = 'accom_to_block'";

    $result = querydatabase($sql);
    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") !== 0) {
        while ($results = $result->fetch_assoc()) {
            return $results['meta_value'];
        }
    } else {
        return "none";
    }
}
