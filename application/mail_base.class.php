<?php
/**
 * Project: xvn.
 * File: mail_base.class.php.
 * Author: Ken Zaki
 * Email: kenzaki@xiao.vn
 * Create Date: 1:56 PM - 7/31/13
 * Website: www.xiao.vn
 */

Class baseMailler {


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
            self::$instance = new baseMailler();
        }
        return self::$instance;
    }

    /**
     *
     * @set undefined vars
     *
     * @param string $index
     *
     * @param mixed $value
     *
     * @return void
     *
     */
	
    function send($sendname,$from,$name,$to,$subject,$content) {
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = MAIL_HOST; // specify main and backup server
        $mail->Port = MAIL_PORT; // set the port to use
        $mail->SMTPAuth = MAIL_AUTH; // turn on SMTP authentication
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; // your SMTP username or your gmail username
        $mail->Password = MAIL_PASS; // your SMTP password or your gmail password
        $mail->From = $from;
        $mail->FromName = $sendname; // Name to indicate where the email came from when the recepient received
        $mail->AddAddress($to,$name);
		//$mail->AddCC("sangtd@xiao.vn","Sang, Thai Dinh");
        //$mail->AddAddress("sangtd@xiao.vn","Sang, Thai Dinh");
        $mail->AddReplyTo($from,$sendname);
        $mail->WordWrap = 50; // set word wrap
        $mail->IsHTML(true); // send as HTMLs
        $mail->Subject = $subject;
        $mail->Body = $content;
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    //send email register new account student   
    public  function sendStudentPasswordEmail($name, $to, $username, $password, $subject)
	{
		if (!defined('MAIL_PASS') || MAIL_PASS === '') {
			return false;
		}

		require_once __SITE_PATH . '/application/phpmailer.class.php';
		require_once __SITE_PATH . '/application/smtp.class.php';

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = MAIL_HOST;
		$mail->Port = MAIL_PORT;
		$mail->SMTPAuth = MAIL_AUTH;
		$mail->SMTPSecure = MAIL_SECURE;
		$mail->Username = MAIL_ACC;
		$mail->Password = MAIL_PASS;
		$mail->CharSet = 'UTF-8';
		$mail->From = MAIL_ACC;
		$mail->FromName = 'Trường Cao đẳng Kon Tum';
		$mail->AddAddress($to, $name);
		$mail->AddReplyTo(MAIL_ACC, 'Trường Cao đẳng Kon Tum');
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $this->getStudentPasswordEmailTemplate($name, $username, $password);
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
	}
    public  function sendVerifyEmail($name, $to, $token,$subject)
	{
		if (!defined('MAIL_PASS') || MAIL_PASS === '') {
			return false;
		}

		require_once __SITE_PATH . '/application/phpmailer.class.php';
		require_once __SITE_PATH . '/application/smtp.class.php';

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = MAIL_HOST;
		$mail->Port = MAIL_PORT;
		$mail->SMTPAuth = MAIL_AUTH;
		$mail->SMTPSecure = MAIL_SECURE;
		$mail->Username = MAIL_ACC;
		$mail->Password = MAIL_PASS;
		$mail->CharSet = 'UTF-8';
		$mail->From = MAIL_ACC;
		$mail->FromName = 'Trường Cao đẳng Kon Tum';
		$mail->AddAddress($to, $name);
		$mail->AddReplyTo(MAIL_ACC, 'Trường Cao đẳng Kon Tum');
		$mail->IsHTML(true);
		$mail->Subject = $subject; 
		$mail->Body = $this->getEmailTemplate($name, $to, $token);
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
	}
    // Template for new account email
    private function getEmailTemplate($name, $email, $token)
	{
		return 
		
		'<div style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,sans-serif;">
        <div style="max-width:620px;margin:30px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);">
            
            <div style="background:linear-gradient(135deg,#0d4e96,#1976d2);padding:28px;text-align:center;color:#fff;">
                <h2 style="margin:0;font-size:24px;">Cổng thông tin việc làm</h2>
                <p style="margin:8px 0 0;font-size:14px;">Thông tin tài khoản đăng nhập</p>
            </div>

            <div style="padding:32px;color:#333;">
                <h3 style="margin-top:0;color:#0d4e96;">Xin chào '.$name.',</h3>

                <p style="font-size:15px;line-height:1.6;">
                   Chúc mừng bạn đã đăng ký tài khoản tại Cổng thông tin việc làm Trường Cao đẳng Kon Tum thành công! Vui lòng xác thực email của bạn bằng cách nhấp vào liên kết bên dưới.
                </p>

                <div style="background:#f0f6ff;border:1px solid #d8e8ff;border-radius:12px;padding:20px;margin:24px 0;">
                    <p style="margin:0 0 10px ;border-bottom:1px solid #d8e8ff;padding-bottom:10px;"><b>Email đăng ký:</b> '.$email.'</p>
                    
                </div>
                <p style="color:#b42318"><strong>Lưu ý:</strong> Vui lòng xác thực email của bạn trước khi đăng nhập.</p>
                <div style="text-align:center;margin:30px 0;">
                    <a href="'.XC_URL.'/verify_email/'.$token.'"
                       style="background:#0d4e96;color:#fff;text-decoration:none;padding:14px 28px;border-radius:30px;font-weight:bold;display:inline-block;">
                        Xác thưc email ngay
                    </a>
                </div>

                <p style="font-size:14px;color:#777;">
                  Email này được gửi tự động, vui lòng không trả lời.
                </p>
            </div>

            <div style="background:#f1f3f6;padding:18px;text-align:center;font-size:13px;color:#777;">
                © '.date('Y').' Cổng thông tin việc làm. All rights reserved.
            </div>
        </div>
    </div>';
	}
    private function getPasswordEmailTemplate($name, $email, $token)
	{
		return 
		
		'<div style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,sans-serif;">
        <div style="max-width:620px;margin:30px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);">
            
            <div style="background:linear-gradient(135deg,#0d4e96,#1976d2);padding:28px;text-align:center;color:#fff;">
                <h2 style="margin:0;font-size:24px;">Cổng thông tin việc làm</h2>
                <p style="margin:8px 0 0;font-size:14px;">Thông tin tài khoản đăng nhập</p>
            </div>

            <div style="padding:32px;color:#333;">
                <h3 style="margin-top:0;color:#0d4e96;">Xin chào '.$name.',</h3>

                <p style="font-size:15px;line-height:1.6;">
                    Tài khoản của bạn đã được tạo thành công. Vui lòng sử dụng thông tin bên dưới để đăng nhập hệ thống.
                </p>

                <div style="background:#f0f6ff;border:1px solid #d8e8ff;border-radius:12px;padding:20px;margin:24px 0;">
                    <p style="margin:0 0 10px ;border-bottom:1px solid #d8e8ff;padding-bottom:10px;"><b>Tên đăng nhập:</b> '.$username.'</p>
                    <p style="margin:0;"><b>Mật khẩu:</b> 
                        <span style="font-size:18px;color:#d35400;font-weight:bold;">'.$password.'</span>
                    </p>
                </div>
                <p style="color:#b42318"><strong>Lưu ý:</strong> Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu.</p>
                <div style="text-align:center;margin:30px 0;">
                    <a href="'.XC_URL.'"
                       style="background:#0d4e96;color:#fff;text-decoration:none;padding:14px 28px;border-radius:30px;font-weight:bold;display:inline-block;">
                        Đăng nhập ngay
                    </a>
                </div>

                <p style="font-size:14px;color:#777;">
                  Email này được gửi tự động, vui lòng không trả lời.
                </p>
            </div>

            <div style="background:#f1f3f6;padding:18px;text-align:center;font-size:13px;color:#777;">
                © '.date('Y').' Cổng thông tin việc làm. All rights reserved.
            </div>
        </div>
    </div>';
	}
	function newaccount($name,$to,$username,$activelink) 
	{
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = MAIL_HOST; 
        $mail->Port = MAIL_PORT; 
        $mail->SMTPAuth = MAIL_AUTH;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; 
        $mail->Password = MAIL_PASS;
        $mail->From = "no-reply@xiao.vn";
        $mail->FromName = "Seagull CRM"; 
        $mail->AddAddress($to,$name);
        $mail->AddReplyTo("sangtd@xiao.vn","Seagull CRM");
        $mail->WordWrap = 50; 
        $mail->IsHTML(true); 
        $mail->Subject = "Kích hoạt tài khoản mới Seagull CRM";
		$tpl = $this->mail("new_account");
		$tpl = str_replace('%%NAME%%', $name, $tpl);
		$tpl = str_replace('%%USERNAME%%', $username, $tpl);
		$tpl = str_replace('%%ACTIVE_LINK%%', $activelink, $tpl);
        $mail->Body = $tpl;
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
	function cmsn($name,$to) 
	{
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = MAIL_HOST; 
        $mail->Port = MAIL_PORT; 
        $mail->SMTPAuth = MAIL_AUTH;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; 
        $mail->Password = MAIL_PASS;
        $mail->From = "no-reply@xiao.vn";
        $mail->FromName = "Seagull CRM"; 
        $mail->AddAddress($to,$name);
        $mail->AddReplyTo("sangtd@xiao.vn","Seagull CRM");
        $mail->WordWrap = 50; 
        $mail->IsHTML(true); 
        $mail->Subject = "Seagull Hotel - Chúc mừng sinh nhật anh Duy Nhân";
		$tpl = $this->mail("cmsn");
        $mail->Body = $tpl;
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
	function send2($sendname,$from,$name,$to,$subject,$content) {
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = MAIL_HOST; // specify main and backup server
        $mail->Port = MAIL_PORT; // set the port to use
        $mail->SMTPAuth = MAIL_AUTH; // turn on SMTP authentication
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; // your SMTP username or your gmail username
        $mail->Password = MAIL_PASS; // your SMTP password or your gmail password
        $mail->From = $from;
        $mail->FromName = $sendname; // Name to indicate where the email came from when the recepient received
        $mail->AddAddress($to,$name);
        $mail->AddReplyTo($from,$sendname);
        //$mail->WordWrap = 50; // set word wrap
        $mail->IsHTML(true); // send as HTMLs
        $mail->Subject = $subject;
		$tpl = $this->mail("return_notification_email");
        $mail->Body = $tpl;
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
	function sendtask($sendname,$from,$name,$to,$subject,$content) {
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = MAIL_HOST; // specify main and backup server
        $mail->Port = MAIL_PORT; // set the port to use
        $mail->SMTPAuth = MAIL_AUTH; // turn on SMTP authentication
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; // your SMTP username or your gmail username
        $mail->Password = MAIL_PASS; // your SMTP password or your gmail password
        $mail->From = $from;
        $mail->FromName = $sendname; // Name to indicate where the email came from when the recepient received
        $mail->AddAddress($to,$name);
        $mail->AddReplyTo("sangtd@xiao.vn",$sendname);
        //$mail->WordWrap = 50; // set word wrap
        $mail->IsHTML(true); // send as HTMLs
        $mail->Subject = $subject;
		$tpl = $this->mail("email-markup");
		 $tpl = str_replace('%%STAFF_NAME%%', $name, $tpl);
        $mail->Body = $tpl;
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    function mail($name) {
        $path = __SITE_PATH . '/template/emails/' . $name . '.html';

        if (file_exists($path) == false)
        {
            //throw new Exception('Template not found in '. $path);
            return "Khong tim thay file";
        }
        return file_get_contents($path);
    }

    public function sendersmtp($name,$to,$type,$content,$value)
    {
        include_once "phpmailer.class.php";
        include_once "smtp.class.php";
        $mail = new PHPMailer();
        $mail->IsSMTP(); // set mailer to use SMTP
        $mail->Host = MAIL_HOST; // specify main and backup server
        $mail->Port = MAIL_PORT; // set the port to use
        $mail->SMTPAuth = MAIL_AUTH; // turn on SMTP authentication
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Username = MAIL_ACC; // your SMTP username or your gmail username
        $mail->Password = MAIL_PASS; // your SMTP password or your gmail password
        //End variable
        $mail->From = "";
        $mail->Subject = "";
        switch($type)
        {
            case "newregister":
            {
                $mail->From = "passport@xiao.vn";
                $mail->Subject = "Thank for signup at Xiao Media Account Gateway!";
                $mail->AddReplyTo("passport@xiao.vn","Xiao Media Inc");
                $mail->FromName = "Xiao Passport Gateway"; // Name to indicate where the email came from when the recepient received
                $mail->AddAddress($to,$name);
                $tpl = $this->mail("general_email");
                $tpl = str_replace('%%GLOBAL_EmailHeader%%', "Cổng tài khoản Xiao", $tpl);
                $tpl = str_replace('%%GLOBAL_EmailMessage%%', "Đây là một thông báo thử nghiệm từ Xiao Media", $tpl);
                $tpl = str_replace('%%GLOBAL_EmailFooter%%', "Thông báo này được gửi đến email: ".$to." vì đã đăng ký nhận tin", $tpl);
                $mail->Body = $tpl;
				break;
            }
            case "forgotpass":
            {
                $mail->From = "passport@xiao.vn";
                $mail->Subject = "Forgot password - Xiao Media Account Gateway!";
                $mail->AddReplyTo("passport@xiao.vn","Xiao Media Corporation");
                $mail->FromName = "Xiao Passport Gateway"; // Name to indicate where the email came from when the recepient received
                $mail->AddAddress($to,$name);
                $tpl = $this->mail("forgotpass_email");
                $tpl = str_replace('%%GLOBAL_EmailHeader%%', "Cổng tài khoản Xiao", $tpl);
                $tpl = str_replace('%%GLOBAL_PasswordLink%%', $value, $tpl);
                $tpl = str_replace('%%GLOBAL_EmailFooter%%', "Thông báo này được gửi đến email: ".$to." vì đã đăng ký nhận tin", $tpl);
                $mail->Body = $tpl;
				break;
            }
            case "newpass":
            {
                $mail->From = "passport@xiao.vn";
                $mail->Subject = "Forgot password - Xiao Media Account Gateway!";
                $mail->AddReplyTo("passport@xiao.vn","Xiao Media Corporation");
                $mail->FromName = "Xiao Passport Gateway"; // Name to indicate where the email came from when the recepient received
                $mail->AddAddress($to,$name);
                $tpl = $this->mail("newpass_email");
                $tpl = str_replace('%%GLOBAL_EmailHeader%%', "Cổng tài khoản Xiao", $tpl);
                $tpl = str_replace('%%GLOBAL_NameTo%%', $name, $tpl);
                $tpl = str_replace('%%GLOBAL_NewPassword%%', $value, $tpl);
                $tpl = str_replace('%%GLOBAL_LoginLink%%', XC_URL."/member/login", $tpl);
                $tpl = str_replace('%%GLOBAL_SupportLink%%', XC_URL."/support", $tpl);
                $tpl = str_replace('%%GLOBAL_EmailFooter%%', "Thông báo này được gửi đến email: ".$to." vì đã đăng ký nhận tin", $tpl);
                $mail->Body = $tpl;
				break;
            }
			case "test":
			{
				$mail->From = "sangtd@xiao.vn";
                $mail->Subject = "Email Tu Ve may Bay Hai Au";
                $mail->AddReplyTo("sangtd@xiao.vn","Ve May Bay Hai Au");
                $mail->FromName = "Xiao Mail Service"; // Name to indicate where the email came from when the recepient received
                $mail->AddAddress($to,$name);
                $tpl = $this->mail("newpass_email");
                $tpl = str_replace('%%GLOBAL_EmailHeader%%', "Cổng tài khoản Xiao", $tpl);
                $tpl = str_replace('%%GLOBAL_NameTo%%', $name, $tpl);
                $tpl = str_replace('%%GLOBAL_NewPassword%%', $value, $tpl);
                $tpl = str_replace('%%GLOBAL_LoginLink%%', XC_URL."/member/login", $tpl);
                $tpl = str_replace('%%GLOBAL_SupportLink%%', XC_URL."/support", $tpl);
                $tpl = str_replace('%%GLOBAL_EmailFooter%%', "Thông báo này được gửi đến email: ".$to." vì đã đăng ký nhận tin", $tpl);
                $mail->Body = $tpl;
				break;
			}
            default:
                break;
        }
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }

}

?>
