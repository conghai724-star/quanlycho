<?php
class baseModel {
	private static $instance;
	
	function __construct() {
		
	}
   public static function getInstance() {
		if (!self::$instance)
		{	
			self::$instance = new baseModel();
		}
		return self::$instance;
	}
	public function get($name){
		$file = __SITE_PATH.'/model/'.str_replace("model","",strtolower($name))."Model.php";
		
		if(file_exists($file))
		{
			// start code hai
			include_once ($file);
			// end code hai
			$class = str_replace("model","",strtolower($name))."Model";
			return new $class;
		}		
		return NULL;
	}
	public function action($id, $row, $table){
		global $db;
		return $db->query("UPDATE ".$table." SET ".$row." = 99 WHERE id = '".$id."'  ");
	}
	
	function __destruct() {
	}
}
