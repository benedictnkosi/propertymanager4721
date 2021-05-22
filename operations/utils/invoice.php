<?php

function createInvoicePDF($to, $guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName, $amountPaid)
{
    $parameters = [
        'from' => 'Renuga Guest House',
        'to' => $guestName . " " . $customerPhone,
        'logo' => "http://renuga.co.za/wp-content/uploads/2020/07/icon.png",
        'number' => $resID,
        'items[0][name]' => $rooName,
        'items[0][quantity]' => $resaNights,
        'items[0][description]' => "Arrival dates: " . $checkin . " \r\n  Departure date: " . $checkout,
        'items[0][unit_cost]' => $price,
        
        
        'notes' => "Thank you for choosing to stay with us! \r\n
\r\n
Please make a payment for the deposit to secure your room. Your room is still bookable on our websites.\r\n
50% Deposit is required to secure the booking.\r\n
We take Card payment (3%), Cash and EFT. We, unfortunately, can not check you in with an outstanding balance.\r\n
Please email proof of payment to info@renuga.co.za\r\n
\r\n
Banking Details\r\n
Bank: FNB\r\n
Name: Renuga Guesthouse\r\n
Acc: 62788863241\r\n
branch: 250 655\r\n
\r\n
See you soon!\r\n
\r\n
",
        'terms' => "No noise after 6pm\r\n
No loud music\r\n
No parties\r\n
No smoking inside the house\r\n
No kids under the age of 12\r\n
Check-in cut-off is at 22:00. Please make arrangements for a later check-in\r\n
The guest can cancel free of charge until 7 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 7 days before arrival. If the guest doesnt show up they will be charged the total price of the reservation.\r\n
        
We look forward to hosting you\r\n
        
Renuga Guest House\r\n
",
        
        "currency" => "ZAR",
        "amount_paid" => $amountPaid
    ];
    
    try {
        $ch = curl_init();
        $fp = fopen($resID . ".pdf", "w");
        
        // set url
        curl_setopt($ch, CURLOPT_URL, "https://invoice-generator.com");
        
        curl_setopt($ch, CURLOPT_POST, 1);
        
        
        //print_r($parameters);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        
        curl_setopt($ch, CURLOPT_FILE, $fp);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        
        
        // $output contains the output string
        
        $output = curl_exec($ch);
        
        fwrite($fp, $output);
        
        fclose($fp);
        
    } catch (Exception $e) {
        echo "oops";
        return false;
    }
    
    return true;
}
