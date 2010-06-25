<?php
/**
 * Database parameters and utility functions
 * @author     Wayne Walls wfwalls(at)gmail(dot)com
 */


if (!defined('SERVERCOMM')) {
    die('Unauthorized access');
}


//*
$databaseUser = "root";
$databasePassword = "";
$hostName = "localhost";
$databaseName = "sc_test";
/*/
$databaseUser     = "";
$databasePassword = "";
$hostName         = "";
$databaseName     = "";
//*/


function showerror() {
    $prompt = "We apologize for the inconvenience. <br \>";
    $prompt .= "There has been a problem with our log-in process. <br \>MySQL Error: ";
    /*
    die("$prompt " . mysql_errno() . " : " . mysql_error());
    /*/
    die("$prompt " . mysql_errno() . ".");
    //*/
}


?>
