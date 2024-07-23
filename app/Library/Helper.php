<?php
namespace App\Library;
use App\Model\Permission;
use App\Model\Role;
use App\Model\User;
use Auth;
use Config;
use DB;
use Illuminate\Support\Facades\Session;
 
class Helper {

	//send otp function
	public static function sendOTP($user){
		
    }

	//  send otp forgot 
	public static function sendOTPForgotPassword($user)
	{
	    
	}

	//  send forgot password
	public static function sendForgotPassword($user)
	{
	    
	    
	}	




	public static function generateNumber($table, $column) {
		
		$uid = mt_rand(100, 9999999);
		
		return $uid;
		/*do {
			$random = mt_rand(1000, 9999);
			$uid = Config::get('constants.UID.' . $table) . $random;

			$exists = DB::table($table)->where($column, $uid)->count();
		} while ($exists > 0);
		return $uid;*/
	}
	public static function generateReferralCode() {
		do {
			$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$code = substr($letters, mt_rand(0, 24), 2) . mt_rand(1000, 9999) . substr($letters, mt_rand(0, 23), 3) . mt_rand(10, 99);

			$exists = User::where('referral_code', $code)->count();
		} while ($exists > 0);
		return $code;
	}

	public static function generateNumberRole($role) {
		do {
			$random = mt_rand(1000, 9999);
			$uid = strtoupper(substr($role, 0, 3)) . $random;

			$exists = DB::table('users')->where('user_code', $uid)->count();
		} while ($exists > 0);
		return $uid;
	}
	public static function generateCode() {
		 

		return mt_rand(1000, 9999);
	}
	public static function getRoleName($role) {
		$name = Role::where('role', $role)->first()->name;
		return $name;
	}
	public static function compress_image($url, $quality) {

		$info = getimagesize($url);
		if ($info['mime'] == 'image/jpeg') {
			$image = imagecreatefromjpeg($url);
			$url = str_replace('.jpeg', '.jpg', $url);
			imagejpeg($image, $url, $quality);
		} elseif ($info['mime'] == 'image/gif') {
			$image = imagecreatefromgif($url);
			imagegif($image, $url, $quality);
		} elseif ($info['mime'] == 'image/png') {
			$image = imagecreatefrompng($url);
			$quality = floor($quality / 10);
			imagepng($image, $url, $quality);
		}
		return $url;
	}

	public static function checkAccess($route) {
		if (Auth::user()->is_admin=='Yes') {
			return true;
		}
		$url = explode('admin/', $route);
		if (isset($url[1])) {
			$uri = explode('/', $url[1]);			
			$role = Auth::guard('admin')->user()->user_role[0]->id;
			$permission = Permission::where('role_id', $role)->where('module_action_id', $action->id)->where('status', 'AC')->count();
			if ($permission > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}

	}

	public static function get_time_ago($time) {
		$time_difference = time() - strtotime($time);

		if ($time_difference < 1) {
			return '1s ago';
		}
		$condition = array(12 * 30 * 24 * 60 * 60 => 'year',
			30 * 24 * 60 * 60 => 'month',
			24 * 60 * 60 => 'd ago',
			60 * 60 => 'h ago',
			60 => 'm ago',
			1 => 's ago'
		);
		foreach ($condition as $secs => $str) {
			$d = $time_difference / $secs;

			if ($d >= 1) {
				$t = round($d);
				if ($str == 'month' || $str == 'year') {
					return  $t . ' ' . $str;
				}else{
					return  $t.$str;
					//return  $t . ' ' . $str . ($t > 1 ? 's' : '');
				}
				
			}
		}
	}

	public static function format_number($amount) {
		$decimal = (string) ($amount - floor($amount));
		$money = floor($amount);
		$length = strlen($money);
		$delimiter = '';
		$money = strrev($money);

		for ($i = 0; $i < $length; $i++) {
			if (($i == 3 || ($i > 3 && ($i - 1) % 2 == 0)) && $i != $length) {
				$delimiter .= ',';
			}
			$delimiter .= $money[$i];
		}

		$result = strrev($delimiter);
		$decimal = preg_replace("/0\./i", ".", $decimal);
		$decimal = substr($decimal, 0, 3);

		if ($decimal != '0') {
			$result = $result . $decimal;
		}

		return $result;
	}
}

?>