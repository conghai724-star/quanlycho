<?php
/**
 * Project: thuvien.
 * File: crm.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 11:11 - 20/10/2013
 * Website: www.xiao.vn
 */
Class crm{
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
            self::$instance = new crm();
        }
        return self::$instance;
    }
	public function get_list_provice($country)
	{
		global $db;
		$db->query("SELECT * FROM sgt_province WHERE country = '".$country."'");
		return $db->fetch_object(false);
	}
	private function checknumber($cusnumber)
	{
		global $db;
		$db->query("SELECT * FROM sgt_customers WHERE customer_number = '".$cusnumber."'");
		if($db->num_row())
        {
            return false;
        }
        else
        {
            return true;
        }
	}
	private function generateRandomString($length = 10) 
	{
		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
			return $randomString;
	}
	public function newcustomernumber()
	{
		$newid = "HA".$this->generateRandomString(8);
		do
		{
			$newid = "HA".$this->generateRandomString(8);
		}
		while(!$this->checknumber($newid));
		return $newid;
	}
	public function get_list_company()
	{
		global $db;
		$db->query("SELECT * FROM sgt_company");
		return $db->fetch_object(false);
	}
	
	public function get_list_user($group)
	{
		global $db;
		if($group == "*")
		{
			$db->query("SELECT * FROM sgt_users");
		}
		else
		{
			$db->query("SELECT * FROM sgt_users WHERE user_group = '".$group."'");
		}
		return $db->fetch_object(false);	
	}
	public function get_list_district($province)
	{
		global $db;
		$db->query("SELECT * FROM sgt_district WHERE province = '".$province."'");
		return $db->fetch_object(false);
	}
	public function get_list_menu()
	{
		global $db;
		$db->query("SELECT * FROM sgt_menu ORDER BY menutype");
		return $db->fetch_object(false);
	}
	public function get_list_res()
	{
		global $db;
		$db->query("SELECT * FROM sgt_rest");
		return $db->fetch_object(false);
	}
	public function get_list_source()
	{
		global $db;
		$db->query("SELECT * FROM sgt_customer_source");
		return $db->fetch_object(false);
	}
	public function get_recent_event($type,$limit = 4)
	{
		global $db;
		if($type == 0)
		{
			$db->query("SELECT * FROM sgt_events ORDER BY event_date ASC, event_type ASC LIMIT ".$limit);
		}
		else
		{
			$db->query("SELECT * FROM sgt_events WHERE event_type = '".$type."' ORDER BY event_date ASC, event_type LIMIT ".$limit);
		}
		return $db->fetch_object(false);
	}
	public function fullname($cid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_customers WHERE id = '".$cid."'");
		$c = $db->fetch_object(true);
		return $c->firstname." ".$c->lastname;
	}
	public function companyname($cid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_company WHERE id = '".$cid."'");
		$c = $db->fetch_object(true);
		return $c->name;
	}
	public function companyshortname($cid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_company WHERE id = '".$cid."'");
		$c = $db->fetch_object(true);
		return $c->shortname;
	}
	public function get_list_customers()
	{
		global $db;
		$db->query("SELECT * FROM sgt_customers");
		return $db->fetch_object(false);
	}
	public function get_functionsheet($invoice)
	{
		global $db;
		$db->query("SELECT * FROM sgt_event_booking WHERE event_invoiceno = '".$invoice."'");
		return $db->fetch_object(true);
	}
	public function get_menuid_by_name($menuname)
	{
		global $db;
		$db->query("SELECT * FROM sgt_menu WHERE title = '".$menuname."' LIMIT 1");
		return $db->fetch_object(true)->id;
	}
	public function get_res_name($id)
	{
		global $db;
		$db->query("SELECT * FROM sgt_rest WHERE id = '".$id."' LIMIT 1");
		return $db->fetch_object(true)->title;
	}
	public function get_menu_by_invoice($invoice,$type)
	{
		global $db;
		if($type == "drink")
		{
			$db->query("SELECT *, m.title as menutitle,m.price as price FROM sgt_event_menus as em
			INNER JOIN sgt_menu as m ON em.menuid = m.id
			WHERE invoiceid = '".$invoice."' AND m.menutype = '10'");
		}
		else
		{
			$db->query("SELECT *, m.title as menutitle,m.price as price FROM sgt_event_menus as em
			INNER JOIN sgt_menu as m ON em.menuid = m.id
			WHERE invoiceid = '".$invoice."' AND NOT(m.menutype = '10')");
		}
		
		return $db->fetch_object(false);
	}
}