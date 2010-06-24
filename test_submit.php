<?PHP
/**
 * The script receives an ajax request for processing
 *
 * @author Wayne Walls <wwalls@anacapasciecnes.com>
 */


define('SC_TEST', 1);


/**
 * authenticate here
 * invoke die("auth failure") if authentication fails
 */


// load utility functions
require_once('common.php');


// setup the database
//$hostname         = "";
//$databaseUser     = "";
//$databasePassword = "";
require_once('database.php');

//setup a database connection
if (!$connection = @ mysql_connect($hostName, $databaseUser, $databasePassword)) {

    // showerror();
    die("database failure");
}

//select the database to be used in this script
if (!mysql_selectdb($databaseName, $connection)) {

    // showerror();
    die("database failure");
}


// remove magic quotes if magic quotes is ON
if (get_magic_quotes_gpc()) {
    if (!empty($_GET))     remove_magic_quotes($_GET);
    if (!empty($_POST))    remove_magic_quotes($_POST);
    if (!empty($_COOKIE))  remove_magic_quotes($_COOKIE);
    if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);

    //turn magic quotes off
    @ini_set('magic_quotes_gpc', 0);

    //let the rest of the script find out if quotes have been stripped
    define('MAGIC_QUOTES_STRIPPED', 1);
}


//decode elements of $_GET
if (!empty($_GET)) {
    decode_url($_GET);
}


// get $_GET ready for use
if (!empty($_GET)) {
    decode_url($_GET);
    check_for_html($_GET);
    trim_whitespace($_GET);
    clean_for_mysql($_GET);
}
// get $_POST ready for use
if (!empty($_POST)) {
    // if $_POST contains JSON process with ENT NOQUOTES to leave quotes intact
    foreach ($_POST as $key => $value)
    {
        $_POST[$key] = htmlspecialchars($_POST[$key], ENT_NOQUOTES);
    }
    // if there is no JSON then use ENT QUOTES
    //check_for_html($_POST);

    trim_whitespace($_POST);
    clean_for_mysql($_POST);
}


// get $_REQUEST ready for use
// don't let array indices in $_COOKIE interfere with $_REQUEST elements -- remove $_COOKIE from $_REQUEST
$_REQUEST = array_merge($_GET, $_POST);


// ensure that strings are strings HERE
// cast $var as a string using $var = (string) $var
if (isset($_REQUEST['test_value'])) {
    $_REQUEST['test_value'] = (string) $_REQUEST['test_value'];
}
else
{
    // if we don't have what we expect in $_POST then die
    die("POST failure");
}


// ensure that numbers are numbers HERE
// cast $var as an int using $var = (int) $var
if (isset($_REQUEST['test_id'])) {
    $_REQUEST['test_id'] = (int) $_REQUEST['test_id'];
}
else
{
    // if we don't have what we expect in $_POST then die
    die("POST failure");
}

/*
sleep(10);
//*/

// store the new submission in the database
$time = gmmktime();
$query = "INSERT INTO test_table VALUES (
        NULL,
        '{$_REQUEST['test_id']}',
        '{$_REQUEST['test_value']}',
        '{$time}')";


// Execute the query
if (!$result = @ mysql_query($query, $connection)) {

    // showerror();
    die("database failure");
}
else
{
    echo("success");
}

exit;


?>
