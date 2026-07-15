<?php
/**
 * Project: thuvien.
 * File: crm.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 11:11 - 20/10/2013
 * Website: www.xiao.vn
 */
Class hr{
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
            self::$instance = new hr();
        }
        return self::$instance;
    }
	public function get_list_departments()
	{
		global $db;
		$db->query("SELECT * FROM sgt_departments ORDER BY id DESC");
		return $db->fetch_object(false);
	}
	public function get_list_group()
	{
		global $db;
		$db->query("SELECT * FROM sgt_user_groups ORDER BY id DESC");
		return $db->fetch_object(false);
	}
	public function departname($teamid)
	{
		
	}
	public function shorten_name($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_users WHERE id = '".$userid."'");
		$user = $db->fetch_object(true);
		$lastname1 = explode(" ", $user->lastname);
		$shrname = general::getInstance()->bodau_ten($lastname1[count($lastname1)-1])." ".substr(general::getInstance()->bodau_ten($user->firstname),0,1);
		for($i = 0;$i<count($lastname1) -1 ;$i++)
		{
			$shrname .= "".substr(general::getInstance()->bodau_ten($lastname1[$i]),0,1);
		}
		return $shrname;
	}
	public function lastname($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_users WHERE id = '".$userid."'");
		$user = $db->fetch_object(true);
		$lastname1 = explode(" ", $user->lastname);
		return $lastname1[count($lastname1) - 1];
	}
	public function fullname($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_users WHERE id = '".$userid."'");
		$user = $db->fetch_object(true);
		return $user->firstname." ".$user->lastname;
	}
	public function get_running_task($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_task_assigns as a
		INNER JOIN sgt_tasks as t ON a.taskid = t.id
		WHERE a.assigned_staff = '".$userid."' AND NOT(t.task_status = 3)");
		return $db->fetch_object(false);
	}
	public function get_deadline_task($userid)
	{
		global $db;
		$db->query("SELECT * FROM sgt_task_assigns as a
		INNER JOIN sgt_tasks as t ON a.taskid = t.id
		WHERE a.assigned_staff = '".$userid."' AND t.task_status = 2
		ORDER BY t.due_time DESC LIMIT 1");
		return $db->fetch_object(true);
	}
	public function statistic($userid,$key)
	{
		global $db;
		$db->query("SELECT * FROM sgt_user_statistic WHERE userid = '".$userid."'");
		return $db->fetch_object(true)->$key;
	}
	public function tasktype($type)
	{
		switch($type)
		{
			case "1":
				return "Not Started";
				break;
			case "2":
				return "In Processing";
				break;
			case "3":
				return "Completed";
				break;
			case "4":
				return "Cancelled";
				break;
			default:
				return "";
				break;
		}
	}
	public function typelabel($progress)
	{
		if($progress == 0)
		{
			return  "bg-important";
		}
		elseif($progress < 70)
		{
			return  "bg-warning";
		}
		elseif($progress >70 && $progress <100)
		{
			return  "bg-info";
		}
		else
		{
			return  "bg-success";
		}
	}
	public function priority($prio)
	{
		if($prio == "0")
		{
			return  "Lowest";
		}
		elseif($prio == "1")
		{
			return  "Low";
		}
		elseif($prio == "2")
		{
			return  "Medium";
		}
		elseif($prio == "3")
		{
			return  "High";
		}
		else
		{
			return  "Highest";
		}
	}
}