<?php

//error_reporting(-1);
/**
 * @author Rahul <rahul@hostnsoft.com> , Rohan  * 
 */
#=================================================================================#
include_once("/home/vtermina/public_html/newapi/db_class.php");
global $insert_id;
$insert_id = 0;

class GVN_CODE_CLASS extends DB_CLASS {

    var $callURL = "TRUE";
    var $user_email;

    function insert_report($tempdb, $tonum, $num, $msg, $keyword, $status, $time) {
        global $insert_id;
        //$smsobj	=	new sendsms_class();//class object
        $dbh = $this->connect_db($tempdb);
        $msg = mysql_real_escape_string($msg);
        $keyword = mysql_real_escape_string($keyword);
        $sql = "INSERT INTO `longcode_report` (`tonum`, `from`, `message`, `keyword`,`status`,`timestamp`) VALUES ('" . $tonum . "','" . $num . "','" . $msg . "','" . $keyword . "','" . $status . "','" . $time . "');";
        $result = mysql_query($sql, $dbh) or ($error = (mysql_error()));
        $id = mysql_insert_id($dbh);
        if (!$result)
            return 0; //$error;
        else {
            $insert_id = $id; //mysql_insert_id($dbh);
            mysql_close();
            return 1;
        }
    }

    //Now not in use
    //Fetch Keyword Details
    function get_keyword_details($keyword, $tonum) {
        //$smsobj	=	new sendsms_class();//class object
        $row = array();
        $dbh = $this->connect_db(1);
        $keyword = mysql_real_escape_string($keyword, $dbh);
        $sql = "SELECT * FROM `ms_longcode_keyword` WHERE LOWER(`keyword`) LIKE '" . $keyword . "' and (longcode= '" . $tonum . "' or (concat('91',longcode)= '" . $tonum . "'))";
        
        $result = mysql_query($sql, $dbh) or $error = (mysql_error());
        if ($result) {
            if (mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result);
                return $row;
            } else {
                //No user Found with this keyword
                //die("Unable To Load Your Profile Details");	
            }
        }
    }

    function update_balance($user_pid, $keyword, $tonum) {
        global $insert_id;
        $id = $insert_id;
        //$smsobj	=	new sendsms_class();//class object
        $sql = "SELECT * FROM `ms_user` WHERE  user_pid = '" . $user_pid . "' limit 1";
        $dbh = $this->connect_db(1);
        $result = mysql_query($sql, $dbh) or $error = (mysql_error());
        mysql_close($dbh);
        if ($result) {
            if (mysql_num_rows($result) > 0) {
                //global $user_email;
                $row = mysql_fetch_array($result);
                $this->user_email = $email = $row['user_email'];

                if ($row['user_lc_inbox_bal'] < 75) {
                    $this->user_email = $row['user_email'];
                    $header = 'MIME-Version: 1.0' . "\n";
                    $header .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
                    $from = '';
                    $header .= 'From: ' . $from . "\n";
                    mail($this->user_email, "LongCode Low balance alert for { $keyword }", 'Low balance in long code 8082000222. You have very low balance in INBOX. Please contact your account manager');
                }
                if ($row['user_lc_inbox_bal'] > 0) {
                    $sql = "update ms_user set user_lc_inbox_bal=(user_lc_inbox_bal-1) WHERE user_pid = '" . $user_pid . "' limit 1";
                    $dbh = $this->connect_db(1);
                    $result = mysql_query($sql, $dbh) or $error = (mysql_error());
                    mysql_close($dbh);
                } else {
                    $this->callURL = 'FALSE';
                    //Hide MEssage from user ser status=0
                    $sql = "update longcode_report set status=1 WHERE id='" . $id . "'";
                    $dbh = $this->connect_db(1);
                    $result = mysql_query($sql, $dbh) or $error = (mysql_error());
                    mysql_close($dbh);
                    //Notify user with mail 
                    $this->user_email = $row['user_email'];
                    //$user_email=$row['user_email'];
                    $header = 'MIME-Version: 1.0' . "\n";
                    $header .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
                    $from = '';
                    $header .= 'From: ' . $from . "\n";
                    mail($this->user_email, "Regarding No balance in LongCode 8082000222", 'You are receving some sms on { ' . $keyword . ' }. but you do not have sufficient INBOX balance. Caution:1 Your URL is not called by our system incase you don\'t have balance. 2 You can not see you sms in Inbox');
                }
            } else {
                //die('user not found');
            }
        }
    }

    function send_gvn($keyword_detail, $req_mobiles, $msg, $tonum, $keyword, $refNum, $time) {
        $callURL = $this->callURL;
        $user_email = $this->user_email;
        $req_message = $keyword_detail['message'];
        $req_sender = substr($keyword_detail['senderid'], 0, 8);
        $_SESSION['pid'] = $keyword_detail['user_pid'];
        $num = $req_mobiles;
        if (strlen($keyword_detail['user_pid']) < 1) {
            die("Unable To Load Your Profile Details...".print_r($keyword_detail,1));
        }
        if (isset($keyword_detail['email']) && strlen($keyword_detail['email']) > 1) {
            $header = 'MIME-Version: 1.0' . "\n";
            $header .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
            $from = 'no-reply@vtermination.com';
            $header .= 'From: ' . $from . "\n";
            $email = filter_var($keyword_detail['email'], FILTER_SANITIZE_EMAIL);
            mail($email, "New sms received on your longcode keyword { $keyword }", 'MobilePhone: ' . $req_mobiles . ' <br />Message: ' . $msg . ' <br />To your longcode: ' . $tonum, $header);
        }
        //OA:919886645580-MR:120131101734755739-Time:10:17:34:841274   response for provider
        //echo ' url '.$keyword_detail['url'].'  url*  ';
        if (isset($keyword_detail['url']) && strlen($keyword_detail['url']) > 1 && $callURL == 'TRUE') {
            $connect_url = $keyword_detail['url']; // Do not change
            $param["number"] = $num; //
            $param["keyword"] = $keyword; //  
            $param["message"] = $msg;

            include_once(CLASS_DIR . "ClassLongCode.php");
            $lcodeobj = new longCodeClass();
            $circle = $lcodeobj->getMobileCircle($num);
            $cirArr = explode("-", $circle);
            $param["operator"] = $cirArr[0];
            $param["circle"] = $cirArr[1];

            $request = '';
            foreach ($param as $key => $val) {
                $request.= $key . "=" . urlencode($val);
                $request.= "&";
            }
            $request = substr($request, 0, strlen($request) - 1);
            $url2 = $connect_url . "?" . $request;
            $ch = curl_init($url2);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $curl_scraped_page = curl_exec($ch);

            /* Check for 404 (file not found). */
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            mail("rahul@hostnsoft.com","httpCode $httpCode LongCode URL not found for { $keyword }" ,'Long code 8082000222. Your Callback URL '.$connect_url.' is not working please check. Error Code : '.$httpCode.' .'.$curl_scraped_page."  url ".$url2);
            if ($httpCode != 200) {
                /* Handle 404 here. */
                //$email=$row['user_email'];
                $header = 'MIME-Version: 1.0' . "\n";
                $header .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
                $from = '';
                $header .= 'From: ' . $from . "\n";
                mail($user_email, "LongCode URL not found for { $keyword }", 'Long code 8082000222. Your Callback URL ' . $connect_url . ' is not working please check. Error Code : ' . $httpCode . ' .');
            }
            curl_close($ch);
            //echo $curl_scraped_page;
        }
        //die();
        $_SESSION['redirect_url'] = "9200001164.php";
        //print_r($_SESSION);
        if (strlen($req_sender) > 1 && strlen($req_message) > 1) {
            $connect_url = 'http://india.msg91.com/gvn/sendsms.php'; // Do not change			
            $param['message'] = $keyword_detail['message'];
            $param['sender'] = substr($keyword_detail['senderid'], 0, 8);
            $param['mobiles'] = $num;

            $param['pid'] = $keyword_detail['user_pid'];

            if (substr($num, 0, 2) == '91')
                $param['route'] = 'Template';
            else
                $param['route'] = 'default';

            $param['isLongcodeReply'] = 1;

            foreach ($param as $key => $val) {
                $request.= $key . "=" . urlencode($val);
                $request.= "&";
            }
            $request = substr($request, 0, strlen($request) - 1);
            $url2 = $connect_url . "?" . $request;
            $ch = curl_init($url2);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $curl_scraped_page = curl_exec($ch);
            curl_close($ch);
            $response = $curl_scraped_page;

            if (strlen($response) != 24) {
                mail("rahul@hostnsoft.com", "Longcode SMS received but SendSMS failed", ' url ' . $url2 . ' Keyword ' . $keyword . ' Sender Id:' . $req_sender . ' response ' . $response . ' To Number: ' . $req_mobiles . ' USerr Id' . $_SESSION['pid'] . ' To num ' . $tonum . ' Message : ' . $keyword_detail['message']);
            }
        }
        echo 'OA:' . $tonum . '-MR:' . $refNum . '-Time:' . $time;
        //mail("rahul@hostnsoft.com","process longcode sms sending fail",$response.$_REQUEST['sender'].$_REQUEST['mobiles'].$_SESSION['pid'].$keyword.$tonum.' Response: '.$response);
        exit();
    }

}

$gvnClsObj = new GVN_CODE_CLASS();
# Class GVN_CODE_CLASS Ends Here

if (isset($_REQUEST['user']) && isset($_REQUEST['pass'])) {
    if ($_REQUEST['user'] == '1168751' && $_REQUEST['pass'] == 'qwe123') {
        //mail("rahul@hostnsoft.com"," routo long code received ",' password  '.($_REQUEST['pass']).' username '.$_REQUEST['user'].' DCS '.$_REQUEST['dcs'].'    Orginator '.$_REQUEST['org'].' Message '.$_REQUEST['message'].' Response.');
        $_REQUEST['DestAddr'] = $_REQUEST['dest'];
        $_REQUEST['Req'] = $_REQUEST['message'];
        $_REQUEST['MobNo'] = $_REQUEST['org'];
        $_REQUEST['RefNum'] = date('H:i:s') . ':' . substr((string) microtime(), 2, 6);
        ;
    }
    else
        die();
}

//http://control.msg91.com/gvn/8082000222.php?DestAddr=918082000222&MobNo=919886645580&Req=Test&RefNum=120131101734755739
$num = $_REQUEST['MobNo']; //Requeting user Mobile Number
$tonum = $_REQUEST['DestAddr']; //Our receiver Number
$msg = $_REQUEST['Req']; //Message
//mail('rahul@hostnsoft.com','GVN Array Received','Number: '.$_REQUEST['number'].' Message.'.$msg.' Actual: '.$_REQUEST['Req'].' mob  '.$_REQUEST['MobNo']."  tonum ".$tonum);

if (is_array($msg)) {
    $msg = implode(' ,  ', $msg); //if message is array convert it into string.
    mail('rahul@hostnsoft.com', 'GVN Array Received', 'Number: ' . $_REQUEST['number'] . ' Message.' . $msg . ' Actual: ' . $_REQUEST['Req'] . ' mob  ' . $_REQUEST['MobNo']);
}
$refNum = $_REQUEST['RefNum']; //Unique Refrence Number by provider
$time = date('H:i:s') . ':' . substr((string) microtime(), 2, 6); //Create time to return to provider
$keyword = strtok($msg, " "); //break the string
$keyword = strtolower($keyword); //echo $tonum.$num.$msg.$keyword.$status.$time;
//$msg=substr($msg, strlen($keyword));
//$insert_id=0;

//$callURL='TRUE';//Consider user have balance and we have to perform all operations
//$user_email='';
//echo $keyword.$tonum;
$keyword_detail=$gvnClsObj->get_keyword_details($keyword,$tonum);
//$keyword_detail['user_pid'] = 47217; //rocket

if($tonum=='919200001164')
$keyword_detail['user_pid'] = 52230; //Dbcorp
if($tonum=='919200001174')
$keyword_detail['user_pid'] = 52294; //dbcorpcg
if($tonum=='919200012345')
$keyword_detail['user_pid'] = 56302; //dbcorpcg

if($tonum=='919200078500')
{
    $keyword_detail['user_pid'] = 55398; //dbcorpcg
    
    if($keyword=='mtopup'){
        // megasmsalerts
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $keyword_detail['user_pid'] = 45383;
        $msg = substr($msg, strlen($keyword));
        
    }
    if($keyword=='os'){
        // snyadav
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $keyword_detail['user_pid'] = 57443;
        $msg = substr($msg, strlen($keyword));
        
    }
    if($keyword=='az'){
        // almask
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $keyword_detail['user_pid'] = 58756;
        $msg = substr($msg, strlen($keyword));
        
    }
    /*
     * added by dheeraj on 13-12-13 as per chinmaya request
     * for user 54537 and 59404
     */
    if($keyword=='7snosal' || $keyword=='7ssale'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 54537;
    }
    if($keyword=='GFTOPS' || $keyword=='GFREGS' || $keyword=='GFTRAN' || $keyword=='GFBALR' || $keyword=='GFBALD' || $keyword=='GFTOPD'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 59404;
    }
    if($keyword=='mobpay'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 56927;
    }
    if($keyword=='interested'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 58905;
    }
    if($keyword=='course' || $keyword=='interest'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 60649;
    }
    if($keyword=='dothis'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 59468;
    }
    if($keyword=='FW' || $keyword=='fw' || $keyword=='Fw'){
        // younus
        $keyword_detail=$gvnClsObj->get_keyword_details($keyword,'918082000222');
        $msg = substr($msg, strlen($keyword));
        $keyword_detail['user_pid'] = 56764;
    }
}

$status = 0;
$result = $gvnClsObj->insert_report(1, $tonum, $num, $msg, $keyword, $status, $time); //insert Messge into Longcode_reports table
//
//
//if($tonum=='919200078500')
//$keyword_detail['user_pid'] = 55398; //dbcorpcg
//
//
//print_r($keyword_detail);
//$_REQUEST['message']=$keyword_detail['message'];
//$_REQUEST['sender']=substr($keyword_detail['senderid'],0,8);

//mail('rahul@hostnsoft.com','GVN Array Received',print_r($keyword_detail,1).'Number: '.$_REQUEST['number'].' Message.'.$msg.' Actual: '.$_REQUEST['Req'].' mob  '.$_REQUEST['MobNo']."  tonum ".$tonum);
$_SESSION['pid'] = $keyword_detail['user_pid'];
$gvnClsObj->update_balance($keyword_detail['user_pid'], $keyword, $tonum);
$_REQUEST['mobiles'] = $num;
if(strlen($keyword_detail['url'])<3)
    $keyword_detail['url'] = "http://sms.groupbhaskar.com/smsfeednew.php";
//$keyword_detail['url']="http://p";

$gvnClsObj->send_gvn($keyword_detail, $_REQUEST['mobiles'], $msg, $tonum, $keyword, $refNum, $time);

#================================================================================#

die();
exit();
?>