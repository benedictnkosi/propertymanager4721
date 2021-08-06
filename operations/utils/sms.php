<?php

require_once (__DIR__ . '/../app/application.php');


function send_message_bulk_sms ( $post_body) {
    
    $ch = curl_init( );
    $headers = array(
        'Content-Type:application/json',
        'Authorization:Basic '. base64_encode(USERNAME.":".PASSWORD)
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt ( $ch, CURLOPT_URL, BULK_SMS_URL );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
    // Allow cUrl functions 20 seconds to execute
    curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
    // Wait 10 seconds while trying to connect
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
    $output = array();
    

    $output['server_response'] = curl_exec( $ch );
    $curl_info = curl_getinfo( $ch );
    $output['http_status'] = $curl_info[ 'http_code' ];
    $output['error'] = curl_error($ch);
    
    
    curl_close( $ch );

    return $output;
}


function send_message ( $phoneNumber, $message) {
    
   
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://rest.smsportal.com/v1/BulkMessages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\"sendOptions\":{\"duplicateCheck\":\"none\",\"campaignName\":\"aluve\",\"testMode\":false},\"messages\":[{\"landingPageVariables\":{\"variables\":{},\"landingPageId\":\"1\"},\"content\":\"".$message."\",\"destination\":\"".$phoneNumber."\"}]}",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: BASIC OTc1M2U4ZGYtNWUzYy00ZjY3LTlmMDUtM2I5MDBhNTRiZjkzOmdmNHdTb3gxMno3V2xVZ3FXZ0FPd3NyNWRHVit6Q3Iv",
            "Content-Type: text/json"
        ],
    ]);
    
    $output = array();
    $output['server_response'] = curl_exec( $curl );
    $curl_info = curl_getinfo( $curl );
    $output['http_status'] = $curl_info[ 'http_code' ];
    $output['error'] = curl_error($curl);

    //print_r($output['server_response']);
   // echo "status is " .$output['http_status'];
    
    curl_close($curl);
    
    return $output;
    
}
?>
