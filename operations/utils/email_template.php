<?php

/**
 * handles the reading of the template and replacing all parameters.
 * sampel array to pass. the function will replace <<client_name>> with Benedict
 * $Parameters = array(
 "client_name" => "Benedict",
 "client_surname" => "Nkosi",
 );
 */

//generate_email_body("password_reset", $Parameters);

function generate_email_body($templateName, $Parameters){
	$templateString = readTemplateFile($templateName);
	return replaceParameters($templateString, $Parameters);
}

function replaceParameters($templateString, $Parameters){
	try{
		$bodytag = $templateString;

		foreach ($Parameters as $key => $value) {
			$bodytag = str_replace("<<" . $key . ">>", $value , $bodytag);
		}

		return $bodytag;
	}catch (Exception $e) {
		return $e->getMessage();
	}
}


function readTemplateFile($templateName){

	try{

		$myfile 		= fopen(__DIR__.'/../email_template/' . $templateName . ".html", "r") or die("Unable to open file!");
		$templateString =  fread($myfile,filesize(__DIR__.'/../email_template/' . $templateName . ".html"));

		fclose($myfile);
		return $templateString;

	}catch (Exception $e) {
		return $e->getMessage();
	}

}