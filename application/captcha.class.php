<?php
/**
 * Project: thuvien.
 * File: captcha.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 14:59 - 22/10/2013
 * Website: www.xiao.vn
 */
Class captcha{


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
            self::$instance = new captcha();
        }
        return self::$instance;
    }
    public function create_captcha($codename)
    {
        //include_once "captcha.php";
        //$_SESSION['captcha'] = simple_php_captcha();
    }
}