<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../app/application.php');

createTable();

function createTable()
{
    echo '<h5> Cleaning Report</h5>';
    echo '<table style="width:100%; margin-top:1em">
  <tr>
    <th>Room Name</th>
    <th>Last Cleaned</th>
    <th>Bed Protectors</th>
    <th>Throw</th>
    <th>Duvet inner</th>
    <th>Couch</th>
    <th>Mattress</th>
  </tr>';
    
    getDataForEachAccomodation();
    
    echo '</table>';
}

function getDataForEachAccomodation()
{
    $sql = "SELECT m1.*, post_title
FROM completed_checklist m1 LEFT JOIN completed_checklist m2
 ON (m1.accom_id = m2.accom_id AND m1.idcompleted_checklist < m2.idcompleted_checklist)
 LEFT JOIN wpky_posts ON wpky_posts.id = m1.accom_id
WHERE m2.idcompleted_checklist IS NULL";

    $result = querydatabase($sql);
    $rsType = gettype($result);

    if (strcasecmp($rsType, "string") == 0) {
        echo 'no cleaning checklist found';
        exit();
    } else {
        $roomsToCleanArray = array();
        while ($results = $result->fetch_assoc()) {
            
            $now = new DateTime();
            $date = new DateTime($results["timestamp"]);
            $lastCleaningDate = $date->diff($now)->format("-%dd");
            $days = (int)$date->diff($now)->format("%d");
            $lastCleanDateClassName = "";
            if($days > 3){
                $lastCleanDateClassName='class="red-td"';
            }
            
            $bedProtectorClassName = "";
            $throwClassName = "";
            $duvetInnerClassName = "";
            $couchClassName = "";
            $mattressClassName = "";
            
            $accomName = $results["post_title"];
            $bedProtector = (int)getLastCleaningDateForItem("Bed_Protectors", $results["accom_id"]);
            $throw = (int)getLastCleaningDateForItem("Throw", $results["accom_id"]);
            $duvetInner = (int)getLastCleaningDateForItem("Duvet_inner", $results["accom_id"]);
            $couch = (int)getLastCleaningDateForItem("Couch_cleaning", $results["accom_id"]);
            $mattress = (int)getLastCleaningDateForItem("Mattress", $results["accom_id"]);
            
            
            if($bedProtector > MAX_PROTECTOR_CLEANING_DAYS) $bedProtectorClassName = 'class="red-td"';
            if($throw > MAX_PROTECTOR_CLEANING_DAYS) $throwClassName = 'class="red-td"';
            if($duvetInner > MAX_DUVET_CLEANING_DAYS) $duvetInnerClassName = 'class="red-td"';
            if($couch > MAX_COUCH_CLEANING_DAYS) $couchClassName = 'class="red-td"';
            if($mattress > MAX_MATTRESS_CLEANING_DAYS) $mattressClassName = 'class="red-td"';
            
            echo '<tr>
                    <td>'.$accomName.'</td>
                    <td '.$lastCleanDateClassName.'>'.$lastCleaningDate.'</td>
                    <td '.$bedProtectorClassName.'>-'.$bedProtector.'d</td>
                    <td '.$throwClassName.'>-'.$throw.'d</td>
                    <td '.$duvetInnerClassName.'>-'.$duvetInner.'d</td>
                    <td '.$couchClassName.'>-'.$couch.'d</td>
                    <td '.$mattressClassName.'>-'.$mattress.'d</td>
                  </tr>';
        }
    }
}

function getLastCleaningDateForItem($item, $accomoId)
{
    $sql = "SELECT * from completed_checklist 
    where `checklist` LIKE '%" . $item . "%'
    and `accom_id` = " . $accomoId . "
    order by `timestamp` desc
    limit 1";
    
    $result = querydatabase($sql);
    $rsType = gettype($result);
    
    if (strcasecmp($rsType, "string") == 0) {
        return "-999";
    } else {
        while ($results = $result->fetch_assoc()) {
            $now = new DateTime();
            $date = new DateTime($results["timestamp"]);
            return $date->diff($now)->format("%d");
        }
    }
    
}

