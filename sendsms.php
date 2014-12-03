<?php

include_once("/home/vtermina/public_html/newapi/mongoSendSms.php");
$smsobj = new mongo_sendsms_class(); //class object
$_SESSION['pid'] = $_REQUEST['pid'];
$_SESSION['uty'] = '3';
$_SESSION['user_country'] = '91';

$sql = "SELECT * FROM ms_user WHERE user_pid=" . $_SESSION['pid'] . " ";

$dbh = $smsobj->connect_db();
$result = mysql_query($sql, $dbh);
mysql_close($dbh);
$row = mysql_fetch_assoc($result);

include_once(ROOT_DIR . "newapi/user_login_class.php");
$login_obj = new user_login_class();
$login_obj->set_session_names('check_apiuser', $row);

$_REQUEST['route'] = '4';
$_REQUEST['campaign_name'] = 'LongCodeReply';
$_REQUEST['unicode'] = 0;
$_REQUEST['time'] = 1;

$sendSmsParam = array('api' => 1, 'requestId' => '');

echo $smsobj->sendSmsValidation($sendSmsParam);
?>