<?PHP
/**
 * The script receives an ajax request for processing
 *
 * @author Wayne Walls wfwalls(at)gmail(dot)com
 */


if (function_exists ('ini_set'))
{
   //Use cookies to store the session ID on the client side
   @ ini_set ('session.use_only_cookies', 1);
   //Disable transparent Session ID support
   @ ini_set ('session.use_trans_sid',    0);
}

session_name("servercomm");
session_start();

function remove_magic_quotes(&$array) {

    foreach ($array as $key => $value) {
        $array[$key] = stripslashes($array[$key]);
    }
}


// remove magic quotes if magic quotes is ON
if (get_magic_quotes_gpc()) {
    if (!empty($_POST))    remove_magic_quotes($_POST);
}


// get $_POST ready for use
if (!empty($_POST)) {
    // if $_POST contains JSON process with ENT NOQUOTES to leave quotes intact
    foreach ($_POST as $key => $value)
    {
        $_POST[$key] = htmlspecialchars($_POST[$key], ENT_NOQUOTES);
    }
}


// ensure that strings are strings HERE
// cast $var as a string using $var = (string) $var
if (isset($_POST['test_value'])) {
    $_POST['test_value'] = (string) $_POST['test_value'];
}
else
{
    // if we don't have what we expect in $_POST then die
    die("POST failure");
}


if ($_POST["test_value"] == "success") {

    echo("success");
}
else if ($_POST["test_value"] == "success_with_data") {

    echo('success| { "key1":"value1", "key2":"value2" } ');
}
else if ($_POST["test_value"] == "failure_success") {

    $_SESSION["retry"] = (!isset($_SESSION["retry"]) || $_SESSION["retry"] == 0) ? 1 : $_SESSION["retry"];

    if ($_SESSION["retry"] < 3) {
        $_SESSION["retry"] = $_SESSION["retry"] + 1;
        //echo($_SESSION["retry"]);
        sleep(8);
    }
    else {
        $_SESSION["retry"] = 0;
        echo('success| { "key1":"value1", "key2":"value2" } ');
    }
}
else if ($_POST["test_value"] == "failure") {

    sleep(8);
}


exit;


?>
