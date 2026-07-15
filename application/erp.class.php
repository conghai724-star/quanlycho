<?php
Class erp{
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
            self::$instance = new erp();
        }
        return self::$instance;
    }
	public function get_object_name($id,$type)
	{
		global $db;
		$res = "";
		$table = "";
		$col = "";
		switch($type)
		{
			case 1:
			{
				//Khách hàng
				$table = "hicrm_customers";
				$col = "customer_name";
				break;
			}
			case 2:
			{
				//NCC
				$table = "hicrm_customers";
				$col = "customer_name";
				break;
			}
			case 3:
			{
				//NCC
				$table = "hicrm_employees";
				$col = "employee_name";
				break;
			}
			default:
			{
				$table = "hicrm_customers";
				$col = "customer_name";
				break;
			}
		}
		$db->query("SELECT ".$col." FROM ".$table." WHERE id = '".$id."' ORDER BY id DESC LIMIT 1");
		return $db->fetch_object(true)->$col;
	}
}