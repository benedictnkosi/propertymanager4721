<?php


require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../app/application.php');


if($_FILES['image']){
    addCustomerIDPicture($_POST["customer_id"]);
}

function addCustomerIDPicture(){
    $valid_extensions = array(
        'jpeg',
        'jpg',
        'png',
        'gif',
        'bmp',
        'pdf'
    ); // valid extensions
    $path = __DIR__ . '/../../../uploads/'; // upload directory
        
    if ($_FILES['image']) {
        echo '1';
        $img = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        
        // get uploaded file's extension
        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        // can upload same image using rand function
        $final_image = uniqid() . "." . $ext;
        // check's valid format
        echo '1';
        if (in_array($ext, $valid_extensions)) {
            $path = $path . strtolower($final_image);
            if (move_uploaded_file($tmp, $path)) {
                updateCustomer($final_image, $_POST["customer_id"]);
            }else{
                  $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "failed to move file"
            );
            echo json_encode($temparray1);
        } else {
            $temparray1 = array(
                'result_code' => 1,
                'result_desciption' => "Invalid file"
            );
            echo json_encode($temparray1);
        }
    }else{
        $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "File not found"
        );
        echo json_encode($temparray1);
    }
}


function updateCustomer($imageName, $customer_id){
    $sqlUpdateCustomer = "update wpky_hb_customers set id_image =  '" . $imageName . "' where id = " . $customer_id;
    $result = updaterecord($sqlUpdateCustomer);
    
    if (strcasecmp($result, "Record updated successfully") == 0) {
        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Successfully updated customer image"
        );
        echo json_encode($temparray1);
    }else{
         $temparray1 = array(
            'result_code' => 1,
            'result_desciption' => "Faield to updated customer image"
        );
        echo json_encode($temparray1);
    }
}

?>
