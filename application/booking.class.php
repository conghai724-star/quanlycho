<?php
/**
 * Project: thuvien.
 * File: crm.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 11:11 - 20/10/2013
 * Website: www.xiao.vn
 */
Class booking{
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
            self::$instance = new booking();
        }
        return self::$instance;
    }
	//Hotel Class 
	public function get_hotel_feature_list($limit = 5)
	{
		global $db;
		$db->query("SELECT * FROM gt_hotels WHERE hotel_status = 1 AND hotel_feature = 1 ORDER BY hotel_booked DESC LIMIT ".$limit);
		return $db->fetch_object(false);
	}
	public function get_room_list($hotelid)
	{
		global $db;
		$db->query("SELECT * FROM gt_hotel_rooms WHERE hotelid = '".$hotelid."' ORDER BY room_type ASC");
		return $db->fetch_object(false);
	}
	public function get_hotel_feature_image($hotelid)
	{
		global $db;
		$db->query("SELECT * FROM gt_hotel_images WHERE hotelid = '".$hotelid."' AND image_feature = 1");
		return $db->fetch_object(true)->image_url;
	}
	public function get_best_price_with_discount($hotelid)
	{
		global $db;
		$db->query("SELECT * FROM gt_hotel_rooms WHERE hotelid = '".$hotelid."' ORDER BY room_fare_normal LIMIT 1");
		$room = $db->fetch_object(true);
		return $room->room_fare_normal*(1 - $room->room_discount/100);
	}
	public function get_best_price($hotelid)
	{
		global $db;
		$db->query("SELECT * FROM gt_hotel_rooms WHERE hotelid = '".$hotelid."' ORDER BY room_fare_normal LIMIT 1");
		$room = $db->fetch_object(true);
		return $room->room_fare_normal;
	}
	public function check_room_availability($roomid, $room_count, $num, $fromday, $today)
	{
		global $db;
		$sql = "SELECT * FROM gt_room_closings WHERE roomid = '".$roomid."' AND from_date between '".$fromday."' and '".$today."'";
		$db->query($sql);
		$closing = $db->fetch_object(false);
		$status = true;
		$maxroom = $room_count;
		foreach($closing as $closer)
		{
			if(($room_count - $closer->stock) < $num)
			{
				$maxroom = 0;
				break;
			}
			else
			{
				$maxroom = $room_count - $closer->stock;
			}
		}
		return $maxroom;
	}
	//End Hotel Class
	public function get_list_booking_by_staff($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_booking WHERE booking_staff = '".$userid."'");
		return $db->fetch_object(false);
	}
	public function get_list_booking_by_all()
	{
		global $db;
		$db->query("SELECT * FROM sgt_booking");
		return $db->fetch_object(false);
	}
	public function get_airline_info($airlineid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_airlines WHERE id = '".$airlineid."' LIMIT 1");
		return $db->fetch_object(true);
	}
	public function booking_status($statusid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_booking_status WHERE id = '".$statusid."' LIMIT 1");
		$status = $db->fetch_object(true);
		return '<span class="badge '.$status->status_color.'">'.$status->status_title.'</span>';
	}
	public function get_first_pax($bookingid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_passengers WHERE booking = '".$bookingid."' ORDER BY id ASC LIMIT 1");
		$pax = $db->fetch_object(true);
		return $pax->paxfirstname." ".$pax->paxlastname;
	}
	public function get_list_rest()
	{
		global $db;
		$db->query("SELECT * FROM sgt_rest");
		return $db->fetch_object(false);
	}
}