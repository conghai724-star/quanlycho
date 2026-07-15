<?php
Class home{


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
            self::$instance = new home();
        }
        return self::$instance;
    }
	public function count_project()
	{
		global $db;
		$db->query("SELECT Count(*) as countdata FROM bds_projects");
		return $db->fetch_object(true)->countdata;
	}
	public function get_post_meta($pid,$key)
	{
		global $db;
		$db->query("SELECT meta_value FROM bds_post_metas WHERE pid = '".$pid."' AND meta_key = '".$key."' LIMIT 1");
		return $db->fetch_object(true)->meta_value;
	}
	public function get_post_by_type($typeid,$limit = 50)
	{
		global $db;
		$db->query("SELECT *, s.id as pid FROM bds_posts as s 
		LEFT JOIN hicrm_provinces as p ON s.post_province = p.id
		LEFT JOIN hicrm_districts as d ON s.post_district = d.id
		LEFT JOIN hicrm_wards as w ON s.post_ward = w.id
		WHERE s.post_type = '".$typeid."'
		ORDER BY s.post_create_time DESC LIMIT ".$limit);
		return $db->fetch_object();
	}
	public function get_menu()
	{
		global $db;
		$db->query("SELECT * FROM bds_menus ORDER BY menu_order ASC LIMIT 5");
		return $db->fetch_object();
	}
	public function check_favorite($pid)
	{
		if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != "")
		{
			global $db;
			$db->query("SELECT * FROM bds_user_favorites WHERE uid = '".$_SESSION['user']['id']."' AND pid = '".$pid."'");
			if($db->num_row())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	public function get_list_categories()
	{
		global $db;
		$db->query("SELECT * FROM bds_categories");
		return $db->fetch_object();
	}
	public function get_list_province()
	{
		global $db;
		$db->query("SELECT * FROM hicrm_provinces");
		return $db->fetch_object();
	}
	public function get_nearby_place($ward,$district = 0)
	{
		global $db;
		$db->query("SELECT * FROM bds_posts WHERE post_ward = '".$ward."' OR post_district = '".$district."'");
		return $db->fetch_object();
	}
	public function get_images($pid)
	{
		global $db;
		$db->query("SELECT * FROM bds_images WHERE pid = '".$pid."'");
		return $db->fetch_object();
	}
	public function get_project_images($pid)
	{
		global $db;
		$db->query("SELECT * FROM bds_project_images WHERE pid = '".$pid."'");
		return $db->fetch_object();
	}
	public function get_feature_images($pid)
	{
		global $db;
		$db->query("SELECT * FROM bds_images WHERE pid = '".$pid."' AND image_feature = 1");
		$url = $db->fetch_object(true)->image_url;
		return XC_URL."/uploads/post/".$url;
	}
	public function get_project_feature_images($pid)
	{
		global $db;
		$db->query("SELECT * FROM bds_project_images WHERE pid = '".$pid."' AND image_feature = 1");
		$url = $db->fetch_object(true)->image_url;
		return XC_URL."/uploads/post/".$url;
	}
	public function get_location_post($pid = "", $address = "",$ward = "",$district = "",$provice = "")
	{
		$result = array();
		if($pid != "")
		{
			global $db;
			$db->query("SELECT post_lat,post_long,post_placeid, post_address, ward_name, district_name, province_name FROM bds_posts as p
			LEFT JOIN hicrm_wards as w ON p.post_ward = w.id
			LEFT JOIN hicrm_districts as d ON p.post_district = d.id
			LEFT JOIN hicrm_provinces as pr ON p.post_province = pr.id
			WHERE p.id = '".$pid."'");
			$post = $db->fetch_object(true);
			if($post->post_lat == "")
			{
				$address = urlencode($post->post_address.", ".$post->ward_name.", ".$post->district_name.", ".$post->province_name);
				$data = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?new_forward_geocoder=true&address=".$address."&key=AIzaSyBa7M8rC66KnI530MPjmQkoI8FaZ02AsrE");
				$data = json_decode($data,true);
				if($data["results"][0]["geometry"]["location"]["lat"] != "")
				{
					$result["lat"] = $data["results"][0]["geometry"]["location"]["lat"];
					$result["long"] = $data["results"][0]["geometry"]["location"]["lng"];
					$result["placeid"] = $data["results"][0]["place_id"];
					$db->query("UPDATE bds_posts SET post_lat = '".$data["results"][0]["geometry"]["location"]["lat"]."', post_long = '".$data["results"][0]["geometry"]["location"]["lng"]."', post_placeid = '".$data["results"][0]["place_id"]."' WHERE id = '".$pid."'");
				}
			}
			else
			{
				$result["lat"] = $post->post_lat;
				$result["long"] = $post->post_long;
				$result["placeid"] = $post->post_placeid;
			}
		}
		else
		{
			$address = urlencode($address.", ".$ward.", ".$district.", ".$province);
			$data = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?new_forward_geocoder=true&address=".$address."&key=AIzaSyBa7M8rC66KnI530MPjmQkoI8FaZ02AsrE");
			$data = json_decode($data,true);
			if($data["results"][0]["geometry"]["location"]["lat"] != "")
			{
				$result["lat"] = $data["results"][0]["geometry"]["location"]["lat"];
				$result["long"] = $data["results"][0]["geometry"]["location"]["lng"];
				$result["placeid"] = $data["results"][0]["place_id"];
			}
			else
			{
				$result["lat"] = "";
				$result["long"] = "";
			}
		}
		return $result;
	}
	//===================== DASHBOARD FUNCTIONS =======================//
	public function count_user($today = "")
	{
		global $db;
		if($today == true)
		{
			$db->query("SELECT count(*) as countdata FROM hicrm_users WHERE DATE(user_register_time) = '".date("Y-m-d")."' AND user_group IN (4,5)");
		}
		else
		{
			$db->query("SELECT count(*) as countdata FROM hicrm_users WHERE user_group IN (4,5)");
		}
		return $db->fetch_object(true)->countdata;
	}
	public function count_post($approved = true)
	{
		global $db;
		if($approved == false)
		{
			$db->query("SELECT count(*) as countdata FROM bds_posts WHERE post_status = 0");
		}
		else
		{
			$db->query("SELECT count(*) as countdata FROM bds_posts WHERE post_status = 1");
		}
		return $db->fetch_object(true)->countdata;
	}
	public function get_list_meta()
	{
		global $db;
		$db->query("SELECT * FROM bds_meta_type");
		return $db->fetch_object();
	}
	public function get_list_permission()
	{
		global $db;
		$db->query("SELECT * FROM hicrm_permissions");
		return $db->fetch_object();
	}
	public function get_list_meta_by_pid($pid)
	{
		global $db;
		$db->query("SELECT * FROM bds_post_metas as mt
		LEFT JOIN bds_meta_type as type ON mt.meta_key = type.meta_key
		WHERE mt.pid = '".$pid."'
		");
		return $db->fetch_object();
	}
	
}




