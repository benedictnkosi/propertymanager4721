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
    
    $apiKey = '9753e8df-5e3c-4f67-9f05-3b900a54bf93';
    $apiSecret = 'udmhiKoD7rjQdPd4haYpnX6ZRdeNJ473';
    $accountApiCredentials = $apiKey . ':' .$apiSecret;
    
    // Convert to Base64 Encoding
    $base64Credentials = base64_encode($accountApiCredentials);
    $authHeader = 'Authorization: Basic ' . $base64Credentials;
    
    //3. Generate an AuthToken
    
    $authEndpoint = 'https://rest.smsportal.com/Authentication';
    
    $authOptions = array(
        'http' => array(
            'header'  => $authHeader,
            'method'  => 'GET',
            'ignore_errors' => true
        )
    );
    $authContext  = stream_context_create($authOptions);
    
    $result = file_get_contents($authEndpoint, false, $authContext);
    
    $authResult = json_decode($result);
    
    //4. Authentication Request
    
    $status_line = $http_response_header[0];
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status = $match[1];
    
    if ($status === '200') {
        $authToken = $authResult->{'token'};
        
        //var_dump($authResult);
    }
    else {
        return false;
    }
    
    //5. Send Request
    
    $sendUrl = 'https://rest.smsportal.com/bulkmessages';
    
    $authHeader = 'Authorization: Bearer ' . $authToken;
    
    $sendData = '{ "messages" : [ { "content" : "'.$message.'", "destination" : "'.$phoneNumber.'" } ] }';
    
    $options = array(
        'http' => array(
            'header'  => array("Content-Type: application/json", $authHeader),
            'method'  => 'POST',
            'content' => $sendData,
            'ignore_errors' => true
        )
    );
    $context  = stream_context_create($options);
    
    $sendResult = file_get_contents($sendUrl, false, $context);
    
    //6. Response Validation
    
    $status_line = $http_response_header[0];
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status = $match[1];
    
    //print_r($sendResult);
    //echo 'status is ' . $status;
    if ($status === '200') {
        return true;
    }
    else {
        return false;
    }
   
}

?>
