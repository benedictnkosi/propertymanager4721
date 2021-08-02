<?php
$imap = imap_open("{mail.aluvegh.co.za:995/pop3/ssl/novalidate-cert}", "info@aluvegh.co.za", "Nhlaka@02");

if( $imap ) {
    
    //Check no.of.msgs
    $num = imap_num_msg($imap);
    
    //if there is a message in your inbox
    if( $num >0 ) {
        //read that mail recently arrived
        
        $overview = imap_fetch_overview($inbox, $i, 0);
        echo $overview[0]->subject . "<BR>";
        echo imap_qprint(imap_body($imap, $num));
    }
    
    //close the stream
    imap_close($imap);
}

?>