<?php
/**
 * Project: thuvien.
 * File: general.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 08:50 - 07/10/2013
 * Website: www.xiao.vn
 */
Class tour{


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
            self::$instance = new tour();
        }
        return self::$instance;
    }
	public function get_images($tourid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_images WHERE tourid = '".$tourid."'");
		return $db->fetch_object(false);
	}
	public function get_place($placeid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_place WHERE id = '".$placeid."' LIMIT 1");
		return $db->fetch_object(true);
	}
	public function tour_schedule_info($tourid,$date)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_schedule WHERE tourd = '".$tourid."' AND start_date = '".$date."' LIMIT 1");
		return $db->fetch_object(true);
	}
	public function tour_recent_schedule($tourid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_schedule WHERE tourid = '".$tourid."' ORDER BY start_date LIMIT 1");
		return $db->fetch_object(true);
	}
	public function get_place_by_keyword()
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_place");
		return "";
	}
	public function get_tour_price($tourid)
	{
		global $db;
		$db->query("SELECT tour_price, tour_promo FROM sgt_tours WHERE tourid = '".$tourid."'");
		$tour = $db->fetch_object(true);
		if($tour->tour_promo == 0)
		{
			return number_format($tour->tour_price, 0, ',', '.').' VNĐ';
		}
		else
		{
			return number_format($tour->tour_promo, 0, ',', '.').' VNĐ';
		}
	}
	public function get_place_id($place_slug)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_place WHERE place_slug = '".$place_slug."'");
		return $db->fetch_object(true);
	}
	public function get_promo_tours($limit = 6)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_schedule as s INNER JOIN sgt_tours as t ON  s.tourid = t.tourid ORDER BY t.id DESC,t.tour_feature LIMIT ".$limit);
		return $db->fetch_object(false);
	}
	public function get_list_place()
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_place ORDER BY id ASC");
		return $db->fetch_object(false);
	}
	public function get_list_service()
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_service");
		return $db->fetch_object(false);
	}
	public function autotourid()
	{
		global $db;
		$db->query("SELECT tourid FROM sgt_tours ORDER BY tourid DESC LIMIT 1");
		$lastid = $db->fetch_object(true)->tourid;
		$lastid = substr($lastid,2,4);
		$lastid += 1;
		return "HA".str_pad($lastid,4,"0", STR_PAD_LEFT);
	}
	public function count_tour_day($tourid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_reservation WHERE tourid = '".$tourid."'");
		return $db->num_row();
	}
	public function get_tour_reservation($tourid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_reservation WHERE tourid = '".$tourid."' ORDER BY dayid");
		return $db->fetch_object(false);
	}
	public function get_children_place($parent)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_place WHERE place_parent = '".$parent."'");
		$data = $db->fetch_object(false);
		$c = array();
		foreach($data as $d)
		{
			array_push($c,$d->id);
		}
		return $c;
	}
	public function get_tours_albums()
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_albums ORDER BY id DESC");
		return $db->fetch_object(false);
	}
	public function get_album_images($albumid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_tour_album_images WHERE albumid = '".$albumid."'");
		return $db->fetch_object(false);
	}
	public function get_list_car()
	{
		global $db;
		$db->query("SELECT * FROM sgt_cars");
		return $db->fetch_object(false);
	}
	public function get_brand($brandid)
	{
		global $db;
		$db->query("SELECT brand_name FROM sgt_car_brand WHERE brandid = '".$brandid."'");
		return $db->fetch_object(true)->brand_name;
	}
}