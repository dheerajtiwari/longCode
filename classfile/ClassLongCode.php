<?php

//echo "in file";
include_once("config.php");
include_once(CLASS_DIR . 'general_class.php');
include_once(ROOT_DIR . 'logmonitor.php');

//echo "link file";
class longCodeClass extends general_function {

    function deleteRecord($id, $userId) {
        /* @author sameer 
         * @created :06-02-2013 to delete the records from the inbox
         * @called :on page /gcom/user/LongCodeRecords.php->/public_html/user/longcode_layer.php
         */
        if (is_numeric($id) && strlen($id) > 0) {
            $con = $this->connect_db();
            $sql_delt = "DELETE FROM longcode_record WHERE id = " . $id . " AND user_id = " . $userId . "";
            $res_delt = mysql_query($sql_delt, $con) or logmonitor("classlongCode", "Error : " . mysql_error());
            mysql_close($con);
            if ($res_delt) {
                return 'success';
            } else {
                return "Error Deleting Record";
            }
        } else
            return "Error Deleting Record";
    }

    function checkKeyWordForLongCode($longCode, $keyWord, $sender, $msg, $url, $email) {
        if (currentIP == pc2smsIP) {
            $con = $this->connect_db(19);
            $sql_chk = "select keyword from ms_longcode_keyword where keyword='" . $keyWord . "' and user_pid = '" . $_SESSION['id'] . "'";
        } else {
            $con = $this->connect_db();
            $sql_chk = "select keyword from ms_longcode_keyword where keyword='" . $keyWord . "' and longcode='" . $longCode . "'";
        }
        $res_chk = mysql_query($sql_chk) or logmonitor("classlongCode", "Error" . mysql_error());
        $error = '';
        $len = strlen(trim($keyWord));
        if (mysql_num_rows($res_chk) <= 0) {
            if (currentIP != pc2smsIP) {
                $sql_bal = "select user_lc_keyword_bal from ms_user where user_pid='" . $_SESSION['id'] . "'";
                $res_bal = mysql_query($sql_bal) or logmonitor("classlongCode", "Error" . mysql_error());
                $row_bal = mysql_fetch_array($res_bal);
                $bal = $row_bal['user_lc_keyword_bal'];
                if ($bal <= 0) {
                    $error = 'insufficient_balance';
                }
                if ($len <= 5) {
                    $balDeduct = (6 - $len);
                    if ($balDeduct > 0 && $balDeduct <= $bal && ($bal - $balDeduct) >= 0) {
                        $up_bl = "update ms_user set user_lc_keyword_bal=user_lc_keyword_bal-$balDeduct where user_pid='" . $_SESSION['id'] . "'";
                        $balResult = mysql_query($up_bl) or $error = "Error" . mysql_error();
                        if ($balResult)
                            $error = 'ok';
                    } else
                        $error = 'insufficient_balance';
                }// End if length<5
                else
                    $error = 'insufficient_balance';
            } else
                $error = 'ok';

            if ($error == 'ok' or $len > 5) {
                if (isset($_SESSION['id']) and $_SESSION['id'] != '') {
                    $ins_key = "insert into ms_longcode_keyword (longcode,keyword,user_pid,senderid,message,url,email) values('" . $longCode . "','" . $keyWord . "','" . $_SESSION['id'] . "','" . $sender . "','" . addslashes($msg) . "','" . $url . "','" . $email . "')";
                    //$ins_key="insert into ms_longcode_keyword (longcode,keyword,user_pid,senderid,message,url) values('".$longCode."','".$keyWord."','".$_SESSION['id']."','".$sender."','".addslashes($msg)."','".$url."')";
                    mysql_query($ins_key) or logmonitor("classlongCode", "Error" . mysql_error());
                    $error = 'ok';
                } else
                    $error = 'Invalid_User';
            }
        } else
            $error = "keyword_available";
        $msg = array('error' => $error);
        echo json_encode($msg);
        mysql_close($con);
    }

    /*
     * @Last updated: vipin 
     * @Date: 12th september 2014
     * @purpose:Modified availability of keyword for PC2SMS panel. 
     *      */

    function checkKeywordAvail($keyWord, $longCode) {
        //More than one user can add same keyword for one longcode number for PC2SMS but not in other panel.
        $con = $this->connect_db();
        if (currentIP == pc2smsIP) {
            $sql = "select keyword from ms_longcode_keyword where keyword='" . $keyWord . "' and user_pid = '" . $_SESSION['id'] . "'";
        } else {

            $sql = "select keyword from ms_longcode_keyword where keyword='" . $keyWord . "' and longcode='" . $longCode . "'";
        }
        $result = mysql_query($sql, $con);
        if ($result) {
            $rows = mysql_num_rows($result);
            if ($rows > 0) {
                $res['result'] = 'Sorry this LongCode Keyword is not Available.';
                $res['resclass'] = 'error';
            } else {
                $res['result'] = 'You Can choose this LongCode Keyword it\'s Available.';
                $res['resclass'] = 'success';
            }
        } else {
            $res['result'] = 'Error in checking availability.';
            $res['resclass'] = 'error';
        }
        return json_encode($res);
    }

    function getLongCodeKeywordBalance() {
        $user_lc_keyword_bal = 0;
        $sql_bal = "select user_lc_keyword_bal from ms_user where user_pid='" . $_SESSION['id'] . "'";
        $con = $this->connect_db();
        $res_bal = mysql_query($sql_bal) or logmonitor("classlongCode", "Error" . mysql_error());
        mysql_close($con);
        if (mysql_num_rows($res_bal) > 0) {
            $row_bal = mysql_fetch_array($res_bal);
            $user_lc_keyword_bal = $row_bal['user_lc_keyword_bal'];
        }
        return $user_lc_keyword_bal;
    }

    function getLongCodeKeyWordsRow($start, $end) {
        $sql_bal = "select * from ms_longcode_keyword where user_pid='" . $_SESSION['id'] . "' limit " . $start . ',' . $end;
        $con = $this->connect_db();
        $res_bal = mysql_query($sql_bal) or logmonitor("classlongCode", "Error" . mysql_error()); //or mail("test.errormail0@gmail.com", __FUNCTION__, mysql_error() . $sql_bal);
        mysql_close($con);
        return $res_bal;
    }

    function getLongCodeKeyWordsRowCount() {
        $sql_bal = "select count(*) from ms_longcode_keyword where user_pid='" . $_SESSION['id'] . "'"; // limit ".$start.','.$end;
        $con = $this->connect_db();
        $res_count = mysql_query($sql_bal) or logmonitor("classlongCode", "Error" . mysql_error() . __LINE__);
        $row = mysql_fetch_row($res_count);
        mysql_close($con);
        return $row[0];
    }

    function getLongCodeInboxBalance($usrid) {
        $user_lc_inbox_bal = 0;
        $sql_bal = "select user_lc_inbox_bal from ms_user where user_pid='" . $usrid . "'";
        $con = $this->connect_db();
        $res_bal = mysql_query($sql_bal) or logmonitor("classlongCode", "Error" . mysql_error());
        mysql_close($con);
        if (mysql_num_rows($res_bal) > 0) {
            $row_bal = mysql_fetch_array($res_bal);
            $user_lc_inbox_bal = $row_bal['user_lc_inbox_bal'];
        }
        return $user_lc_inbox_bal;
    }

    function getLongCodeInboxRow($usrid, $start, $limit) {
        $sql = "SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp,lr.receive_time from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) and lk.user_pid= '" . $usrid . "' and lr.status=0 order by id desc limit " . $start . ',' . $limit;
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        mysql_close($con);
        return $res;
    }

    function get_Total_LongCodeInboxRow($usrid) {
        //$sql="SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and lk.longcode=lr.tonum and lk.user_pid= ".$usrid." and lr.status=0";
        $sql = "SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) and lk.user_pid= '" . $usrid . "' and lr.status=0 ";
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        $rows = mysql_num_rows($res);
        mysql_close($con);
        return $rows;
    }

    function getLongCodeInboxRowDedicated($usrid, $longCode, $start, $limit) {
        $sql = "SELECT * from longcode_report where tonum in ($longCode) and status=0 order by id desc limit " . $start . ',' . $limit;
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        mysql_close($con);
        return $res;
    }

    function get_Total_LongCodeInboxRowDedicated($usrid, $longCode) {
        //$sql="SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and lk.longcode=lr.tonum and lk.user_pid= ".$usrid." and lr.status=0";
        $sql = "SELECT * from longcode_report where tonum in ($longCode) and status=0";
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        $rows = mysql_num_rows($res);
        mysql_close($con);
        return $rows;
    }

    function get_unread_messages($usrid) {
        $sql = "SELECT count(*) as tot from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) and lk.user_pid= " . $usrid . " and lr.status=1";
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        $rows = mysql_fetch_assoc($res);
        mysql_close($con);
        return $rows['tot'];
    }

    function get_unread_messages_dedicated($usrid, $tonum) {
        $sql = "SELECT count(*) as tot from longcode_report lr,  ms_longcode_keyword lk where lr.tonum='" . $tonum . "' and lk.user_pid= " . $usrid . " and lr.status=1";
        $con = $this->connect_db();
        $res = mysql_query($sql) or $error = mysql_error();
        $rows = mysql_fetch_assoc($res);
        mysql_close($con);
        return $rows['tot'];
    }

    function deductBalanceForUnreadMessages($usrid) {

        $long_code = $this->get_user_long_code($usrid);
        $longCode = '';
        if (is_array($long_code)) {

            foreach ($long_code as $tonum) {
                $longCode .= "91" . $tonum . ", ";
            }

            $longCode = substr($longCode, 0, -2);
        } else {
            $longCode = $long_code;
        }
        if (strstr($longCode, "9229224424")) {
            $sql = "select lr.id from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) and lk.user_pid= " . $usrid . " and lr.status=1";
        } else {
            $sql = "SELECT lr.id  from longcode_report lr,  ms_longcode_keyword lk where lr.tonum='" . $longCode . "' and lk.user_pid= " . $usrid . " and lr.status=1";
        }
        $con = $this->connect_db();
        $res = mysql_query($sql, $con) or $error = mysql_error();
        mysql_close($con);
        if (mysql_num_rows($res) > 0) {
            while ($row = mysql_fetch_array($res)) {
                $sql = "select user_lc_inbox_bal from ms_user where user_pid=" . $usrid . "";
                $con = $this->connect_db();
                $result2 = mysql_query($sql, $con) or $error = mysql_error();
                mysql_close($con);
                if (mysql_num_rows($result2) > 0) {
                    $rs = mysql_fetch_array($result2);
                    if ($rs['user_lc_inbox_bal'] > 0) {
                        $sql = "update longcode_report set status=0 where id=" . $row['id'] . "";
                        $con = $this->connect_db();
                        $result3 = mysql_query($sql, $con) or $error = mysql_error();
                        mysql_close($con);
                        if ($result3) {
                            $sql = "update ms_user set user_lc_inbox_bal=(user_lc_inbox_bal-1) WHERE user_pid = '" . $usrid . "' limit 1";
                            $con = $this->connect_db();
                            $result = mysql_query($sql, $con) or $error = mysql_error();
                            mysql_close($con);
                            if ($result)
                                $msg = 'success';
                            else
                                $msg = 'Balance not updated.';
                        } else
                            $msg = 'Longcode Report Not Updated.';
                    }
                    else {
                        $msg = 'Insufficient Balance.';
                        break;
                    }
                } else
                    $msg = 'Error In Fetch Balance.';
            }
        } else
            $msg = 'No Records Found.';

        return $msg;
    }

    function load_keyword_details($key_id) {
        $sql_key = "select * from ms_longcode_keyword where key_id='" . $key_id . "'";
        $con = $this->connect_db();
        $res_key = mysql_query($sql_key) or logmonitor("classlongCode", "Error" . mysql_error());
        mysql_close($con);
        return $res_key;
    }

    function addLongCodeKeywordInfo() {
        $sql = "update ms_longcode_keyword set senderid='" . $_REQUEST['sender_id'] . "',message='" . addslashes($_REQUEST['message']) . "',url='" . $_REQUEST['url'] . "',email='" . $_REQUEST['email_id'] . "' where key_id='" . $_REQUEST['id'] . "' and user_pid='" . $_SESSION['id'] . "'";
        $con = $this->connect_db();
        $res_key = mysql_query($sql) or logmonitor("classlongCode", "Error" . mysql_error());
        mysql_close($con);
        if ($res_key)
            $msg = 'success';
        else
            $msg = 'Error While Adding Keyword Information.';
        echo $msg;
    }

    function deleteLongCodeKeyword() {
        $key_id = $_REQUEST['key_id'];
        if (!isset($key_id) || strlen(trim($key_id)) < 1) {
            
        }
        $sql_key = "select * from ms_longcode_keyword where key_id='" . $key_id . "' and user_pid='" . $_SESSION['id'] . "'";
        $con = $this->connect_db();
        $res_key = mysql_query($sql_key) or $error = ("Error" . mysql_error());
        mysql_close($con);
        if ($res_key && mysql_num_rows($res_key) > 0) {
            $rows = mysql_fetch_assoc($res_key);
            $keyword = $rows['keyword'];
            $sql = "delete from ms_longcode_keyword where key_id='" . $key_id . "' and user_pid='" . $_SESSION['id'] . "'";
            $con = $this->connect_db();
            $res_key = mysql_query($sql) or logmonitor("classlongCode", "Error" . mysql_error());
            mysql_close($con);
            if ($res_key) {
                $sql = "delete from longcode_report  where keyword='" . $keyword . "'";
                $con = $this->connect_db();
                $delete_key = mysql_query($sql) or $error = ("Error" . mysql_error());
                mysql_close($con);

                $msg = 'success';
            } else
                $msg = 'Error While Deleting Keyword Information.';
        } else
            $msg = 'Error While Deleting Keyword Information.';
        echo $msg;
        return;
    }

    function get_user_long_code($uid) {
        if (currentIP == pc2smsIP)
            return $long_code = '07344563';
        if (currentIP == smsebookIP)
            return $long_code = '9699022022';

        $user_details = $this->load_user_details($uid);
        if (mysql_num_rows($user_details) > 0) {
            $user_details_array = mysql_fetch_array($user_details);
            //print_r($user_details_array);
            $user_uid = $user_details_array['user_userid'];
            if ($user_uid == 24 || $uid == 24) {
                $long_code = '8989042929';
            } else if ($uid == 47217) {//$user_uid==47217 ||   Rocket user Name
                $long_code = array();
                $long_code[] = '9200001164';
                $long_code[] = '9200001174';
                $long_code[] = '9200078500';
            } else if ($uid == 52230) {  //dbcorp
                $long_code = array();
                $long_code[] = '9200001164';
            } else if ($uid == 52294) { //dbcorpcg
                $long_code = array();
                $long_code[] = '9200001174';
            } else if ($uid == 55398) { //dbcorprj
                $long_code = array();
                $long_code[] = '9200078500';
            } else if ($uid == 56302) { //dbcorpin
                $long_code = array();
                $long_code[] = '9200012345';
            } else if ($user_uid == 9036 || $uid == 9036) {
                $long_code = array();
                $long_code[] = '558198949706';
                $long_code[] = '558198949709';
            } else if ($user_uid == 17062 || $uid == 17062) {
                $long_code = '64223004080';
            } else if ($user_uid == 33404 || $uid == 33404) {
                $long_code[] = '8082699911';
            } else if ($uid == 45383) {
                $long_code = array();
                $long_code[] = '9229224424';
                $long_code[] = '9200078500';
            } else if ($uid == 57443) {

                $long_code = array();
                $long_code[] = '9229224424';
                $long_code[] = '9200078500';
            } else if ($uid == 58756) {

                $long_code = array();
                $long_code[] = '9229224424';
                $long_code[] = '9200078500';
            } else if ($uid == 54537 || $uid == 59404 || $uid == 58905 || $uid == 60649 || $uid == 56927 || $uid == 59468 || $uid == 56764) {

                /*
                 * added by dheeraj on 13-12-13 as per chinmaya && mona && manish request
                 * for user 54537 and 59404
                 */
                $long_code = array();
                $long_code[] = '9229224424';
                $long_code[] = '9200078500';
            } else { //if($user_uid==2500|| $uid==2500)
                $long_code = array();
                $long_code[] = '9229224424';
                $long_code[] = '447781470658';
            }
//			else
//			{
//				//$long_code='8827775666';//OLD
//				$long_code='9229224424';
//			}
        } else {
            $long_code = 'Service Unavailable';
        }
        return $long_code;
    }

    function exportLCReports($usrid, $keyword, $start, $end) {
        //echo " in function";
        $str = '';
        include_once(CLASS_DIR . "dlr_class.php");
        if (!$dlrobj->check_admin())
            $str.="and lk.user_pid= '" . $usrid . "'";
        $con = $this->connect_db();
        $keyword = mysql_real_escape_string($keyword, $con);
        if ($keyword == '0')
            $sql = "SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp,lr.receive_time from longcode_report lr, ms_longcode_keyword lk where lk.keyword=lr.keyword and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) " . $str . " and lr.status=0 and receive_time>'$start' and receive_time<='$end 23:59:59' order by id desc ";
        else
            $sql = "SELECT lr.tonum,lr.from,lr.message,lr.keyword,lr.timestamp,lr.receive_time from longcode_report lr, ms_longcode_keyword lk where lr.keyword='" . $keyword . "' and (concat('91',lk.longcode)=lr.tonum || lk.longcode=lr.tonum) " . $str . " and lr.status=0 and receive_time>'$start' and receive_time<='$end 23:59:59' order by id desc ";
        $res = mysql_query($sql) or $error = mysql_error();
        mysql_close($con);
        return $res;
    }

    function exporttoCSV($userId, $start, $end, $keyword = "0") {
        /**
         * @author rahul <rahul@hostnsoft.com>
         * @Desc function use to export longcode report between two date and/or for particular keyword
         * @lastModified 01-11-12 By Rahul 
         */
        $starttime = strtotime($start);
        $endtime = strtotime($end);
        if ($endtime < $starttime) {
            echo "Start date can not be greater than end date";
            die();
        }
        $res = $this->exportLCReports($userId, $keyword, $start, $end);
        $str = '<table cellspacing="0">
				<thead><!-- universal table heading -->
	                    <tr>
	                        <td>Long Code</td>
	                        <td>Keyword</td>
	                        <td>Sender</td>
		      <td>Circle</td>
	                        <td class="msgwrap">Message</td>
	                        <td>Date Time</td>	                        
	                    </tr>
	                </thead>';
        $output = "Long Code, Keyword, Sender, Circle, Message, Date Time \n";
        #- sid 24/11/2014 for chinmay's client export report
        $isBjpIndore = false;
        if ($userId == 74846 && currentIP == vterminationIP && $keyword == "BJPIND") {

            $isBjpIndore = true;
            $output = "Long Code, Keyword, Sender, Circle, Message, Date Time,Name,Address,Vidhan Sabha Kshetra \n";
        }
        if ($res && mysql_num_rows($res) > 0) {
            while ($row = mysql_fetch_assoc($res)) {
                if ($isBjpIndore) {
                    $candidateArr = $this->getCandidateDetail($row['message']);
                    $output.=$row['tonum'] . ",\"" . $row['keyword'] . "\",\"" . $row['from'] . "\", \"" . trim($this->getMobileCircle($row['from'])) . " \",\"" . addslashes($row['message']) . "\",\"" . $row['receive_time'] . "\",\"" . $candidateArr['candidate_name'] . "\",\"" . $candidateArr['candidate_addr'] . "\",\"" . $candidateArr['candidate_VSK'] . "\"\n";
                } else {
                    $output.=$row['tonum'] . ",\"" . $row['keyword'] . "\",\"" . $row['from'] . "\", \"" . trim($this->getMobileCircle($row['from'])) . " \",\"" . addslashes($row['message']) . "\",\"" . $row['receive_time'] . "\"\n"; //\"" . $row['timestamp'] . "\",
                }
            }

            //echo $output;
            if ($keyword != 0)
                $nameappend = '';
            else {
                $nameappend = $keyword;
            }
            $file = 'LongCodeReport_' . $keyword . '_' . $start . '_' . $end;

            header("Content-type: application/csv");
            header("Content-disposition: attachment; filename=" . $file . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            print $output;
            exit();
        } else {

            echo $error = 'No record Found to export.';
            die();
        }
    }

    /*
     * sid 24/11/2014
     * function is made for chinmay's client bjpindore only.
     */

    function getCandidateDetail($message) {

        #- remove extra space and comma from message
        $removeMultipleSpace = preg_replace('/[\s]{2,}/', "", $message);
        $removeMultipleComma = preg_replace('/[,]{2,}/', ",", $removeMultipleSpace);
        $removeLastComma = trim($removeMultipleComma, ",");
        #- explode first string with ,
        $candidateDtlArr = explode(",", $removeLastComma);
        $candidateDtlArrCount = count($candidateDtlArr);
        $candidateName = $candidateVSK = $candidateAddr = "";
        if ($candidateDtlArrCount > 2) {
            #- get the name of user
            $candidateName = $candidateDtlArr[0];
            #- get the vidhan sabha kshetra
            $candidateVSK = $candidateDtlArr[$candidateDtlArrCount - 1];
            #- remaining will be the address
            unset($candidateDtlArr[0], $candidateDtlArr[$candidateDtlArrCount - 1]);

            $candidateAddr = implode(",", $candidateDtlArr);
        } else if ($candidateDtlArrCount == 2) {
            #- get the name of user
            $candidateName = $candidateDtlArr[0];
            $candidateAddr = $candidateDtlArr[1];
        } else {
            $candidateAddr = $candidateDtlArr[0];
        }
        return array("candidate_name" => $candidateName, "candidate_addr" => $candidateAddr, "candidate_VSK" => $candidateVSK);
    }

    function exporttoCSVDedicated($longCode, $start, $end, $keyword = 0) {
        /**
         * @author rahul <rahul@hostnsoft.com>
         * @Desc function use to export longcode report between two date and/or for particular keyword
         * @lastModified 01-11-12 By Rahul 
         */
        $starttime = strtotime($start);
        $endtime = strtotime($end);
        if ($endtime < $starttime) {
            echo "Start date can not be greater than end date";
            die();
        }


        $con = $this->connect_db();
        $keyword = mysql_real_escape_string($keyword, $con);
        if ($keyword == '0' || strlen($keyword) == 0)
            $sql = "SELECT * from longcode_report where tonum in ($longCode) and status=0 and receive_time>'$start' and receive_time<='$end 23:59:59' order by id desc ";
        else
            $sql = "SELECT * from longcode_report where tonum in ($longCode) and status=0 and receive_time>'$start' and receive_time<='$end 23:59:59' and keyword='" . $keyword . "'  order by id desc ";


        $res = mysql_query($sql) or $error = mysql_error();
        //echo $error."aa";
        mysql_close($con);
        //return $res;
        //$res=$this->exportLCReports($userId,$keyword,$start,$end);
        $str = '<table cellspacing="0">
				<thead><!-- universal table heading -->
	                    <tr>
	                        <td>Long Code</td>
	                        <td>Keyword</td>
	                        <td>Sender</td>
		      <td>Circle</td>
	                        <td class="msgwrap">Message</td>
	                        <td>Date Time</td>	                        
	                    </tr>
	                </thead>'; //<td>Time</td>
        $output = "Long Code, Keyword, Sender, Provider, Circle, Message, Date Time \n";
        if ($res && mysql_num_rows($res) > 0) {
            while ($row = mysql_fetch_assoc($res)) {
                $circle = trim($this->getMobileCircle($row['from']));
                $cirArr = explode("-", $circle);
                $output.= $row['tonum'] . ", " . preg_replace("/\r\n|\n\r|\n|\r|\,/", " ", $row['keyword']) . ", " . $row['from'] . ", " . $cirArr[0] . ", " . $cirArr[1] . ", " . preg_replace("/\r\n|\n\r|\n|\r|\,/", " ", $row['message']) . ",  " . $row['receive_time'] . " \n"; //" . $row['timestamp'] . ",
            }
            //echo $output;
            if ($keyword != 0)
                $nameappend = '';
            else {
                $nameappend = $keyword;
            }
            $file = 'LongCodeReport_' . $keyword . '_' . $start . '_' . $end;

            header("Content-type: application/csv");
            header("Content-disposition: attachment; filename=" . $file . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $output;
            //print strip_tags($output);
            exit();
        } else {
            echo $error = 'No record Found to export.';
            die();
        }
    }

    function getUserFromKeyword($keyword) {
        $sql = "SELECT user_pid FROM ms_longcode_keyword where keyword='" . $keyword . "'";
        $dbh = $this->connect_db();
        $result = mysql_query($sql, $dbh);
        $row = mysql_fetch_array($result);
        $user_id = $row[0];
        $userResult = $this->load_user_details($user_id);
        $userRow = mysql_fetch_array($userResult);
        $resultArray["userId"] = $userRow['user_pid'];
        $resultArray["userName"] = $userRow['user_uname'];
        return json_encode($resultArray);
    }

    function getMobileCircle($number) {
        if ($number < 7) {
            return $response = "operator not found";
        }
        if ($mobileCircle = apc_fetch('mobileCircle')) {
            
        } else {
            include_once("mobileCircle.php");
            $mobileCircle = mobileCircle();
            apc_store('mobileCircle', $mobileCircle);
        }
        if (strlen($number) == 12 && substr($number, 0, 2) == '91') {
            $half = substr($number, 2, 5);
            $half2 = substr($number, 2, 4);
        } else {
            $half = substr($number, 0, 5);
            $half2 = substr($number, 0, 4);
        }
        if (isset($mobileCircle->$half)) {

            $response = $mobileCircle->$half;
        } else if (isset($mobileCircle->$half2)) {
            $response = $mobileCircle->$half2;
        } else {
            $response = "operator not found";
        }

        return $response;
    }

    function getKeywordDetail($keyword, $userId) {
        $dbh = $this->connect_db();
        $keyword = mysql_real_escape_string($keyword, $dbh);
        $sql = "SELECT * FROM ms_longcode_keyword where keyword='" . $keyword . "' AND user_pid='" . $userId . "'";

        $result = mysql_query($sql, $dbh);
        if (mysql_num_rows($result) > 0) {
            return $row = mysql_fetch_array($result);
        } else {
            return FALSE;
        }
    }

}

$lcodeobj = new longCodeClass();
?>