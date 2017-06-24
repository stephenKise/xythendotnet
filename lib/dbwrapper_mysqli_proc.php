<?php

/**
 * Execute database queries.
 * @param string $sql MySQL statement string.
 * @param bool $die Disconnect if false.
 * @return mixed Result, or array if called during the installer.
 * @todo Remove the $die, seemingly there for nothing at this point.
 */
function db_query($sql, $die = true)
{
    global $session, $dbinfo, $mysqli_resource;
    if (defined("DB_NODB") && !defined("LINK")){
        return array();
    }
    $dbinfo['queriesthishit']++;
    $starttime = getmicrotime();
    $r = mysqli_Query($mysqli_resource, $sql);
    if (!$r && $die === true) {
        if (defined("IS_INSTALLER")) {
            return array();
        }
        else {
            if ($session['user']['superuser'] & SU_DEVELOPER || 1) {
                require_once("lib/show_backtrace.php");
                die(
                    "<pre>".HTMLEntities($sql, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</pre>"
                    .db_error(LINK)
                    .show_backtrace()
                );
            }
            else {
                die("A most bogus error has occurred.  I apologise, but the page you were trying to access is broken.  Please use your browser's back button and try again.");
            }
        }
    }
    $endtime = getmicrotime();
    if ($endtime - $starttime >= 1.00
        && ($session['user']['superuser'] & SU_DEBUG_OUTPUT)) {
        $s = trim($sql);
        if (strlen($s) > 800) {
            $s = substr($s,0,400)." ... ".substr($s,strlen($s)-400);
        }
        debug("Slow Query (".round($endtime-$starttime,2)."s): ".(HTMLEntities($s, ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`n");
    }
    unset($dbinfo['affected_rows']);
    $dbinfo['affected_rows'] = db_affected_rows();
    $dbinfo['querytime'] += $endtime-$starttime;
    return $r;
}

/**
 * Execute database queries.
 * @param string $sql MySQL statement string.
 * @param array $options Variables to escape to assure database safety.
 * @return mixed Result, or array if called during the installer.
 * @todo Use an option to cache the results.
 */
function query($sql, $options = [])
{
    foreach ($options as $val) {
        debug($val);
    }
    if (defined("DB_NODB") && !defined("LINK")) return array();
    global $session,$dbinfo,$mysqli_resource;
    $dbinfo['queriesthishit']++;
    // $fname = DBTYPE."_query";
    $starttime = getmicrotime();
    //$r = $fname($sql);
    $r = mysqli_Query($mysqli_resource, $sql);

    if (!$r && $die === true) {
    if (defined("IS_INSTALLER")){
    return array();
    }else{
    if ($session['user']['superuser'] & SU_DEVELOPER || 1){
    require_once("lib/show_backtrace.php");
    die(
    "<pre>".HTMLEntities($sql, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</pre>"
    .db_error(LINK)
    .show_backtrace()
    );
    }else{
    die("A most bogus error has occurred.  I apologise, but the page you were trying to access is broken.  Please use your browser's back button and try again.");
    }
    }
    }
    $endtime = getmicrotime();
    if ($endtime - $starttime >= 1.00 && ($session['user']['superuser'] & SU_DEBUG_OUTPUT)){
    $s = trim($sql);
    if (strlen($s) > 800) $s = substr($s,0,400)." ... ".substr($s,strlen($s)-400);
    debug("Slow Query (".round($endtime-$starttime,2)."s): ".(HTMLEntities($s, ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`n");
    }
    unset($dbinfo['affected_rows']);
    $dbinfo['affected_rows']=db_affected_rows();
    $dbinfo['querytime'] += $endtime-$starttime;
    return $r;
}

/**
 * Execute database queries, or check to see if we have a result saved.
 * @param string $sql MySQL statement string.
 * @param string $name Name of the result to save.
 * @return array The MySQL result.
 * @param int $duration Length of time in seconds to cache the result.
 */
function &db_query_cached($sql, $name, $duration = 900)
{
    global $dbinfo;
    $data = datacache($name, $duration);
    if (is_array($data)) {
        reset($data);
        $dbinfo['affected_rows'] = -1;
        return $data;
    }
    else{
        $result = db_query($sql);
        $data = array();
        while ($row = db_fetch_assoc($result)) {
            $data[] = $row;
        }
        updatedatacache($name, $data);
        reset($data);
        return $data;
    }
}

/**
 * Grab the database error caused.
 * @return string Error message thrown.
 */
function db_error()
{
    global $mysqli_resource;
    $r = mysqli_error($mysqli_resource);
    if ($r == "" && defined("DB_NODB") && !defined("DB_INSTALLER_STAGE4")) {
        return "The database connection was never established";
    }
    return $r;
}

/**
 * Organize the result into an associative array.
 * @param mixed $result MySQL result to organize sort.
 * @return array Associative array for the query result.
 */
function db_fetch_assoc(&$result)
{
    if (is_array($result)) {
        if (list($key, $val) = each($result)) {
            return $val;
        }
        else {
            return false;
        }
    }
    else{
        $r = mysqli_fetch_assoc($result);
        return $r;
    }
}

/**
 * Grab the most recent id in the database.
 * @return mixed Result of the last id.
 */
function db_insert_id()
{
    global $mysqli_resource;
    if (defined("DB_NODB") && !defined("LINK")) {
        return -1;
    }
    $r = mysqli_insert_id($mysqli_resource);
    return $r;
}

/**
 * Count the number of rows given for a result.
 * @param mixed $result The MySQL result to count.
 * @return int Number of rows returned from the result.
 */
function db_num_rows($result)
{
    if (is_array($result)) {
        return count($result);
    }
    else {
        if (defined("DB_NODB") && !defined("LINK")) {
            return 0;
        }
        $r = mysqli_num_rows($result);
        return $r;
    }
}

/**
 * Count the number of roes effected.
 * @param mixed $link Database connection link.
 * @return int Amount of rows that have changed.
 */
function db_affected_rows($link = false)
{
    global $dbinfo, $mysqli_resource;
    if (isset($dbinfo['affected_rows'])) {
        return $dbinfo['affected_rows'];
    }
    if (defined("DB_NODB") && !defined("LINK")) {
        return 0;
    }
    $r = mysqli_affected_rows($mysqli_resource);
    return $r;
}

/**
 * Connecto to the MySQL database.
 * @param string $host Location of the database.
 * @param string $user Account to connect with.
 * @param string $pass Password of the MySQL user.
 * @return bool Whether the connection has failed or not.
 */
function db_pconnect($host, $user, $pass)
{
    global $mysqli_resource;
    $mysqli_resource = mysqli_connect($host, $user, $pass);
    if($mysqli_resource) {
        return true;
    }
    else {
        return false;
    }
}
function db_connect($host, $user, $pass)
{
    global $mysqli_resource;
    $mysqli_resource = mysqli_connect($host, $user, $pass);
    if($mysqli_resource) {
        return true;
    }
    else {
        return false;
    }
}

function db_get_server_version()
{
    global $mysqli_resource;
    return mysqli_get_server_info($mysqli_resource);
}

function db_select_db($dbname)
{
    global $mysqli_resource;
    $r = mysqli_select_db($mysqli_resource, $dbname);
    return $r;
}

function db_free_result($result)
{
    if (is_array($result)){
        unset($result);
    }
    else {
        if (defined("DB_NODB") && !defined("LINK")) {
            return false;
        }
        mysqli_free_result($result);
        return true;
    }
}

function db_table_exists($tablename)
{
    global $mysqli_resource;
    if (defined("DB_NODB") && !defined("LINK")) {
        return false;
    }
    $exists = $mysqli_resource->Query("SELECT 1 FROM `$tablename` LIMIT 0");
    if ($exists) {
        return true;
    }
    return false;
}

function db_prefix($tablename, $force = false) {
    global $DB_PREFIX;
    if ($force === false) {
        $special_prefixes = array();
        $prefix = $DB_PREFIX;
        if (isset($special_prefixes[$tablename])) {
            $prefix = $special_prefixes[$tablename];
        }
    }
    else {
        $prefix = $force;
    }
    return $prefix . $tablename;
}
?>