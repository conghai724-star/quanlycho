<?php
/**
 * Project: thuvien.
 * File: crm.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 11:11 - 20/10/2013
 * Website: www.xiao.vn
 */
Class sms{
	/*
     * @Variables array
     * @access public
     */
    private static $instance;

    /**
     *
     * @constructor
     *
     * @access public
     *
     * @return void
     *
     */
    function __construct() {

    }
    public static function getInstance() {
        if (!self::$instance)
        {
            self::$instance = new sms();
        }
        return self::$instance;
    }
	public function get_user_info($uid,$info)
	{
		global $db;
		$db->query("SELECT ".$info." FROM sms_users WHERE id = '".$uid."' LIMIT 1");
		return $db->fetch_object(true)->$info;
	}
	public function countsms($uid,$sent_status = '0')
	{
		global $db;
		$db->query("SELECT COUNT(*) as countsms FROM sms_sents WHERE sent_uid = '".$uid."' AND sent_status = '".$sent_status."'");
		return $db->fetch_object(true)->countsms;
	}
	public function countallsms($uid)
	{
		global $db;
		$db->query("SELECT COUNT(*) as countsms FROM sms_sents WHERE sent_uid = '".$uid."'");
		return $db->fetch_object(true)->countsms;
	}
	public function get_list_transactions($uid,$type = '1',$status = '1',$limit = '5')
	{
		global $db;
		$db->query("SELECT * FROM sms_transactions WHERE trans_uid = '".$uid."' AND trans_type = '".$type."' AND trans_status = '".$status."' ORDER BY trans_date DESC LIMIT ".$limit);
		return $db->fetch_object();
	}
	public function get_list_sms_by_sent($uid)
	{
		global $db;
		if($staffid == "*")
		{
			$db->query("SELECT * FROM sgt_sms ORDER BY createdate DESC");
		}
		else
		{
			$db->query("SELECT * FROM sgt_sms WHERE sent_staff = '".$staffid."' ORDER BY createdate DESC");
		}
		return $db->fetch_object(false);
	}
	public function checknetwork($mobile)
	{
		global $db;
		if(substr($mobile,0,2) == "09")
		{
			$db->query("SELECT * FROM sgt_sms_networks WHERE prefix = '".substr($mobile,0,3)."' LIMIT 1");
		}
		else
		{
			$db->query("SELECT * FROM sgt_sms_networks WHERE prefix = '".substr($mobile,0,4)."' LIMIT 1");
		}
		return $db->fetch_object(true)->network;

	}	
	
	public function sendnewsms($to,$content,$sendtime,$type)
	{
		$request = "http://api.xiao.vn/sms.php?action=new&api_key=".SMS_API_KEY."&api_secrect=".SMS_API_SECRECT."&to=".$to."&content=".urlencode($content)."&type=".$type."&sendtime=".$sendtime."&data=xml";
		$respone = file_get_contents($request);
		$smscount = round(count($content)/160);
		$network = $this->checknetwork($to);
		if($network == "1")
		{
			$smscost = "200";
		}
		else
		{
			$smscost = "250";
		}
		global $db;
		if($respone == "101")
		{
			$db->query("INSERT INTO sgt_sms(sent_staff,receiver,createdate,sentdate,content,smscount,smstype,smscost,smsfrom,smsstatus) VALUES('".$_SESSION['xID']."','".$to."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s",strtotime($sendtime))."','".$content."','".$smscount."','".$type."','".$smscost."','0972471059','1')");
		}
		else
		{
			$db->query("INSERT INTO sgt_sms(sent_staff,receiver,createdate,sentdate,content,smscount,smstype,smscost,smsfrom,smsstatus) VALUES('".$_SESSION['xID']."','".$to."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s",strtotime($sendtime))."','".$content."','1','".$type."','0','','0')");
		}
		return $request;
	}
}