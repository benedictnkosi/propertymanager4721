<?php
require_once (__DIR__ . '/../utils/data.php');

if (isset($_GET["accom_id"])) {
    submitCleaningChecklist();
} else if (isset($_GET["cleaning_checklist"])) {
    getCleanningCheckList($_GET["cleaning_checklist"]);
} else {
    $temparray1 = array(
        'result_code' => 1,
        'result_desciption' => "Please provide required data"
    );
    echo serialize($temparray1);
}

function submitCleaningChecklist()
{
    $return_array = array();
    $now = new DateTime();

    $sql = "INSERT INTO `renugtaj_wp163`.`completed_checklist`
(
`accom_id`,
`checklist`,
`checklist_type`,
`timestamp`,
`notes`)
VALUES
(" . $_GET["accom_id"] . ",
'" . implode(",", $_POST["topics"]) . "',
'CLEANING',
'" . $now->format('Y-m-d H:i:s') . "',
'". $_POST["cleaning_notes"] . "');
";

    $resultCreateRes = insertrecord($sql);
    if (strcasecmp($resultCreateRes, "New record created successfully") == 0) {

        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Cleaning checklist captured successfully"
        );
        echo json_encode($temparray1);
    } else {
        
        $pos = strpos($resultCreateRes, "Duplicate entry");
        
        if ($pos === false) {
            
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => $resultCreateRes
            );
            echo json_encode($temparray1);
        }else{
            
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Checklist for this room already captured for today"
            );
            echo json_encode($temparray1);
            
        }
        
    }
}

function getCleanningCheckList($accom_id)
{
    $return_array = array();

    $sql = "SELECT ID, post_title, post_content FROM renugtaj_wp163.wpky_posts where post_type = 'hb_accommodation'
        and ID = " . $accom_id . ";";

    $completedCleaningSql = "SELECT checklist, notes FROM renugtaj_wp163.completed_checklist where accom_id = " . $accom_id . " and timestamp = '" . $_GET["cleaning_date"] . "'";
    // echo $completedCleaningSql;
    $cleaningChecklistItems = array();
    $cleaningNotes = "";

    
    if (strcasecmp($_GET["cleaning_checklist"], "SelectRoom") == 0) {
        return;
    }
    
    if (strcasecmp($_GET["checklist_history"], "yes") == 0) {
        $result = querydatabase($completedCleaningSql);

        $rsType = gettype($result);

        if (strcasecmp($rsType, "string") !== 0){
            while ($results = $result->fetch_assoc()) {
                $checklist = $results["checklist"];
                $cleaningChecklistItems = explode(",", $checklist);
                $cleaningNotes = $results["notes"];
            }
        }else{
            echo "No cleaning checklist found";
            return;
        }
    }

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

            $json = json_decode($results["post_content"]);

            foreach ($json as $key => $val) {
                $isItemChecked = false;
                
                
                if (strcasecmp($_GET["checklist_history"], "yes") == 0) {
                    if(sizeof($cleaningChecklistItems) > 0){
                        foreach ($cleaningChecklistItems as &$cleaningChecklistItem) {
                            if (strcasecmp(str_replace(' ', '_', $cleaningChecklistItem), str_replace(' ', '_', $key)) == 0) {
                                $isItemChecked = true;
                            }
                        }
                        unset($cleaningChecklistItem);
                    }
                    
                }

                if ($isItemChecked) {
                    echo '<input type="checkbox" class="cleaning_checklist" name="cleaning_checklist" value="' . str_replace(' ', '_', $key) . '"  checked>
				    <label for="' . str_replace(' ', '_', $key) . '"> ' . $key . '</label><br>';
                } else {
                    echo '<input type="checkbox" class="cleaning_checklist"  name="cleaning_checklist" value="' . str_replace(' ', '_', $key) . '">
				    <label for="' . str_replace(' ', '_', $key) . '"> ' . $key . '</label><br>';
                }
            }
            
            if(strlen($cleaningNotes) > 1){
                echo "<br/><b>Notes:</b> "  . $cleaningNotes;
            }
            
        }
    }
}
