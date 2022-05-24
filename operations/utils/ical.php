<?php
/**
 * simpleevent.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

/**
 * Simple Event Example
 *
 * Create a simple iCalendar event
 * No time zone specified, so this event will be in UTC time zone
 *
 */
use DateInterval;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Alarm;
use Eluceo\iCal\Domain\ValueObject\Attachment;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

require_once ('data.php');
require_once("zapcallib.php");
require_once __DIR__ . '/../vendor/autoload.php';
    

#createIcal($_GET["accom_id"]);
importIcal($_GET["accom_id"]);


function createIcal($accomodationId){
    $sql = "Select * from reservations where accom_id = " . $accomodationId;
    $results = querydatabase($sql);
    $rsType = gettype($results);
    
    if (strcasecmp($rsType, "string") == 0) {
        echo "No events";
        exit();
    }

    
    // create the ical object
    $icalobj = new ZCiCal("-//Aluve Guesthouse//". $accomodationId . "// EN");
    
    while ($result = $results->fetch_assoc()) {
        $resId = $result["id"];
        $event_start = $result["check_in"] . "00:01:00";
        $event_end = $result["check_out"] . "23:59:00";
        
        $guestName = "Test";
        $guestEmail = "test@gmail.com";
        $title = "Aluve - No Name  - Resa id: ".$resId;
        // date/time is in SQL datetime format

        // create the event within the ical object
        $eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
        
        // add title
        $eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $title));
        
        // add start date
        $eventobj->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($event_start)));
        
        // add end date
        $eventobj->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($event_end)));
        
        // UID is a required item in VEVENT, create unique string for this event
        // Adding your domain to the end is a good way of creating uniqueness
        $uid = date('Y-m-d-H-i-s') . "@demo.icalendar.org";
        $eventobj->addNode(new ZCiCalDataNode("UID:" . $uid));
        
        // DTSTAMP is a required item in VEVENT
        $eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));
        
        // Add description
        $eventobj->addNode(new ZCiCalDataNode("Description:" . ZCiCal::formatContent(
            "NAME: ".$guestName." \nEMAIL: ".$guestEmail)));
        
    }
    
    // write iCalendar feed to stdout
    $icalString =  $icalobj->export();
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=calendar.ics');
    echo $icalString;
    exit;
}

function importIcal($accomodationId){
    $event = new \Eluceo\iCal\Domain\Entity\Event();
}
    
    
    
