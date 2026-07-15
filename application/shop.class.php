<?php
Class shop{


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
            self::$instance = new shop();
        }
        return self::$instance;
    }
	public function get_order_fees($oid)
	{
		global $db;
		$total = 0;
		$db->query("SELECT * FROM ow_orders WHERE id = '".$oid."'");
		$order = $db->fetch_object(true);
		$db->query("SELECT * FROM ow_order_fees WHERE oid = '".$oid."'");
		$anotherfee = $db->fetch_object();
		$total = $order->order_buy_fee + $order->order_service_fee + $order->order_kr_ship_fee + $order->order_global_ship_fee + $order->order_vn_ship_fee + $order->order_check_fee + $order->order_box_fee; 
		foreach($anotherfee as $fee)
		{
			$total += $fee->fee_amount;
		}
		return $total;
	}
}