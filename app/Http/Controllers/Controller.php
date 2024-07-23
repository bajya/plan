<?php

namespace App\Http\Controllers;
use App\Library\Helper;
use App\Library\Notify;
use App\Library\ResponseMessages;
use App\User;
use App\UserOTP;
use App\UserPlan;
use App\UserDevice;
use App\State;
use App\Notificationuser;
use App\ProductFavourite;
use Auth;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function checkUserDevice($id, $device_id, $device_token) {
		if (stripos(url()->current(), 'api') !== false) {
			$device = UserDevice::where('user_id', $id)->where('device_id', $device_id)->whereStatus('active')->first();
			if (!isset($device->id)) {
				return 0;
			}
			return 1;
		} else {
			return 1;
		}
	}
    public function getAddress($lat,$lng)
    {
    	//google map api url
        $url = "https://maps.google.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&key=".env('MAP_API_KEY');

        // send http request
        $geocode = file_get_contents($url);
        $json = json_decode($geocode);
        $getAddresss = $json->results[0]->address_components;
        $address = $json->results[0]->formatted_address;
        $state = '';
        if (!empty($getAddresss)) {
        	foreach ($getAddresss as $key => $value) {
        		if ($value->types[0] == "administrative_area_level_1" || $value->types[0] == "political") {
        			$state = $value->long_name;
              	}
        	}
        }
        if ($state != '') {
        	if (State::select("id")->where('status','active')->where('name', $state)->where('is_allow','false')->first()) {
        		return true;
        	}else{
        		return false;
        	}
        }else{
        	return true;
        }
    }
	public function pushNotificationSendActive($user, $push) {
		try
		{
          	$notification=new Notificationuser();
          	$notification->sender_id = $push['sender_id'];
          	$notification->receiver_id = $user->id;
          	$notification->notification_type = $push['notification_type'];
          	$notification->title = $push['title'];
          	$notification->description = $push['description'];
          	$notification->status = 'active';
          	$notification->save();

          	$sound = true;
          	$alert = true;
	        /*if ($user->sound == 'Yes') {
	            $sound = 'true';
	        }
          	if ($user->alert == 'Yes') {
              	$alert = 'true';
          	}*/
          	//dd($user->devices);
          	$headtitle = ucfirst($push['title']);
		    $extramessage = ucfirst($push['description']);
          	if (isset($user->devices)) {
          		foreach ($user->devices as $k => $v) {
	          		$device_type = isset($v) && !empty($v->device_type) ? $v->device_type : 'android' ;
		          	$apptoken = isset($v) && !empty($v->device_token) ? $v->device_token : '' ;
		          	
					if ($device_type == 'android') {
		              	$this->androidPushNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
		          	}
		          	if ($device_type == 'ios') {
		          		$this->androidPushNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
		              	//$this->sendIosNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
		          	}
	          	}
          	}

			return [];
		} catch (\Exception $ex) {
			return [];
		}
	}
	public function pushNotificationSendGuestActive($device_type, $apptoken, $push) {
		try
		{
          	$sound = true;
          	$alert = true;
          	$headtitle = ucfirst($push['title']);
		    $extramessage = ucfirst($push['description']);
			if ($device_type == 'android') {
              	$this->androidPushNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
          	}
          	if ($device_type == 'ios') {
              	$this->sendIosNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
          	}
			return [];
		} catch (\Exception $ex) {
			return [];
		}
	}
    public function androidPushNotification($token, $title, $extramessage, $sound, $alert)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'title' => $title,
            'sound' => $sound,
            'body' => $extramessage,
            'vibrate' => $alert,
        ];
        $extraNotificationData = ["message" => $notification, "moredata" => $extramessage, 'type' => ''];
        $fcmNotification = [
            //'registration_ids' => $tokenList, 
            'to'        => $token, 
            'notification' => $notification,
            'data' => $extraNotificationData
        ];
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
        $data = json_encode($fcmNotification);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
       // dd($result);
        if ($result === FALSE) {
           // die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
	public function sendIosNotification($token, $title, $extramessage, $sound, $alert)
	{
	    $url = "https://fcm.googleapis.com/fcm/send";
	    $registrationIds = $token;
	    $serverKey =env('FCM_LEGACY_KEY');
	    $body = $extramessage;
	    $notification = array('title' =>$title , 'body' => $body, 'text' => $body, 'sound' => $sound);
	    $arrayToSend = array('to' => $registrationIds, 'notification'=>$notification,'priority'=>'high');
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			        curl_setopt($ch,CURLOPT_POST, true );
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    $result = curl_exec($ch);
	    if ($result === FALSE) 
	    {
	      //  die('FCM Send Error: ' . curl_error($ch));
	    }
	    curl_close( $ch );
	    return $result;
	}	
	public function checkUserActive($userId, $device_id) {
		if ($user = User::where("id", $userId)->first()) {
			if ($user->status == "active") {
				if ($user->is_verified == 1) {
					if ($user->role == 'shop') {
						$count = UserDevice::where('user_id', $userId)->where('device_id', $device_id)->whereStatus('active')->count();
						if ($count <= 0) {
							$this->response = array(
								"status" => 403,
								"message" => ResponseMessages::getStatusCodeMessages(240),
								"data" => null,
								"logout" => 1,
							);
							$this->shut_down();
							exit;
						}else{
							return true;
						}
					} else {
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(403),
							"data" => null,
							"logout" => 1,
						);
						$this->shut_down();
						exit;
					}
					$count = UserDevice::where('user_id', $userId)->where('device_id', $device_id)->whereStatus('active')->count();
					if ($count <= 0) {
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(240),
							"data" => null,
							"logout" => 1,
						);
						$this->shut_down();
						exit;
					}
				} else {
					$currenttime = date('Y-m-d H:i:s');
	            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
					if ($user->is_verified == 0) {
						$otp = new UserOTP;
						$otp->user_id = $user->id;
						$otp->email = $user->email;
						$otp->otp_expire_time = $otp_expire_time;
						$verify_code = Helper::generateCode();
						$otp->code = $verify_code;
						if ($otp->save()) {
							$user->verify_code = $otp->code;
							//Notify::sendMail("emails.resent_otp", $user->toArray(), "Laravel - Verify Otp");
							$this->response = array(
								"status" => 200,
								"timer" => 3,
								"message" => ResponseMessages::getStatusCodeMessages(106),
								"data" => null,
							);
						} else {
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(102),
								"data" => null,
							);
						}
					} else {
						$this->response = array(
							"status" => 105,
							"message" => ResponseMessages::getStatusCodeMessages(105),
							"data" => null,
						);
					}
					$this->shut_down();
					exit;
				}
			} else {
				if ($user->status == 'inactive') {
					$this->response = array(
						"status" => 666,
						"message" => ResponseMessages::getStatusCodeMessages(216),
						"data" => null,
					);
				} elseif ($user->status == 'delete') {
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(217),
						"data" => null,
						"logout" => 1,
					);
				}
				$this->shut_down();
				exit;
			}
		} else {
			$this->response = array(
				"status" => 403,
				"message" => ResponseMessages::getStatusCodeMessages(5),
				"data" => null,
				"logout" => 1,
			);
			$this->shut_down();
			exit;
		}
	}
	public function logoutUserDevice($id, $device_id = null, $device_token = null) {
		Auth::user()->token()->revoke();
		if (isset($device_id) && $device_id != null) {
			$device = UserDevice::where('user_id', $id)->where('device_id', $device_id)->update(['status' => 'delete']);
			return true;
		} else { 
			UserDevice::where('user_id', $id)->whereStatus('active')->update(['status' => 'delete']);
		}
	}
   	public $response = array(
		"status" => 500,
		"message" => "Internal server error",
		"data" => null,
	);
	public $paginate = 10;
    public function checkKeys($input = array(), $required = array()) {
		$existance = implode(", ", array_diff($required, $input));
		if (!empty($existance)) {
			if (count(array_diff($required, $input)) == 1) {
				$this->response = array(
					"status" => 401,
					"message" => $existance . " key is missing",
					"data" => null,
				);
			} else {
				$this->response = array(
					"status" => 401,
					"message" => $existance . " keys are missing",
					"data" => null,
				);
			}
			$this->shut_down();
			exit;
		}
	}
	public function updateUserDevice($id, $device_id, $device_token, $device_type) {
		if (stripos(url()->current(), 'api') !== false) {
			if (($device_type == 'android') || ($device_type == 'ios') || ($device_type == 'web')) {
				if (($device_type == 'android') || ($device_type == 'ios')) {
					$device = UserDevice::where('user_id', $id)->where('device_id', $device_id)->first();
					if (empty($device)) { 
						$user = User::where('id', $id)->first();
						if (!empty($user)) {
							$updateFav = array('user_id' => $user->id);
							ProductFavourite::where('device_id', $device_id)->where('user_id', 0)->update($updateFav);
							if ($user->status == 'active') {
								
								$notification_count = 0;
								
								$push = array('sender_id' => 1, 'notification_type' => 'logout', 'notification_count' => $notification_count, 'title' => 'You are login another device', 'description' => 'You are login another device same login details');
								//$this->pushNotificationSendActiveLogout($user, $push);
								//UserDevice::where('user_id', $id)->update(['status' => 'delete']);
								$device = new UserDevice;
								$device->user_id = $id;
								$device->device_id = $device_id;
								$device->device_token = $device_token;
								$device->device_type = $device_type;
								
							    return $device->save();
							    
							} else { 
								if ($user->status == 'inactive') {
									$this->response = array(
										"status" => 666,
										"message" => ResponseMessages::getStatusCodeMessages(216),
										"data" => null,
										
									);
									$this->shut_down();
								exit;
								} else{
									$this->response = array(
										"status" => 666,
										"message" => ResponseMessages::getStatusCodeMessages(217),
										"data" => null,
										//"logout" => 1,
									);
									$this->shut_down();
								exit;
								}
							}
							
						}else{
							/*$push = array('sender_id' => $user->id, 'notification_type' => 'logout', 'title' => 'You are login another device', 'description' => 'You are login another device same login details');

							$this->pushNotificationSendActiveLogout($user, $push);
							UserDevice::where('user_id', $id)->where('status', 'active')->update(['status' => 'delete']);*/
							$this->response = array(
								"status" => 403,
								"message" => ResponseMessages::getStatusCodeMessages(5),
								"data" => null,
								"logout" => 1,
							);
							$this->shut_down();
								exit;
						}
					}else{
						//echo 'hh'; die;
						$user = User::where('id', $id)->first();
						if (!empty($user)) {
							$updateFav = array('user_id' => $user->id);
							ProductFavourite::where('device_id', $device_id)->where('user_id', 0)->update($updateFav);
							if ($user->status == 'active') {
								$device->user_id = $id;
								$device->device_id = $device_id;
								$device->device_token = $device_token;
								$device->device_type = $device_type;
								$device->status = 'active';
							    return $device->save();
								/*$push = array('sender_id' => $user->id, 'notification_type' => 'logout', 'title' => 'You are login another device', 'description' => 'You are login another device same login details');

								$this->pushNotificationSendActiveLogout($user, $push);
								UserDevice::where('user_id', $id)->where('status', 'active')->update(['status' => 'delete']);*/
							} else { 
								if ($user->status == 'inactive') {
									$this->response = array(
										"status" => 666,
										"message" => ResponseMessages::getStatusCodeMessages(216),
										"data" => null,
										
									);
									$this->shut_down();
								exit;
								} else{
									$this->response = array(
										"status" => 403,
										"message" => ResponseMessages::getStatusCodeMessages(217),
										"data" => null,
										"logout" => 1,
									);
									$this->shut_down();
								exit;
								}
							}
						}else{
							$this->response = array(
								"status" => 403,
								"message" => ResponseMessages::getStatusCodeMessages(5),
								"data" => null,
								"logout" => 1,
							);
							$this->shut_down();
								exit;
							/*$push = array('sender_id' => $user->id, 'notification_type' => 'logout', 'title' => 'You are login another device', 'description' => 'You are login another device same login details');

							$this->pushNotificationSendActiveLogout($user, $push);
							UserDevice::where('user_id', $id)->where('status', 'active')->update(['status' => 'delete']);*/
						}
					}
				}else{
					return true;
				}
			}else{
				$this->response = array(
					"status" => 515,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
					"logout" => 1,
				);
				$this->shut_down();
				exit;
			}
		} else {
			return true;
		}
	}
	function shut_down(Request $request = null,$userid=null) {
		/*if (isset($request->user_id) && $request->user_id != 0) {
			$user = User::where("id", $request->user_id)->first();*/
			if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
				if ($user->status == 'active') {
					
					$this->response['isPlanExpire'] = !empty(UserPlan::select('id')->where('user_id', $user->id)->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('status', 'active')->first()) ? true : false;
					$this->response['isSubscriptionExpire'] = $user->subscription_id != '' ? true : false;
					$this->response['unread_notification'] = 0;
					$this->response['logout'] = 0;
					
				}else{
					$this->response['status'] = 403;
					$this->response['message'] = ResponseMessages::getStatusCodeMessages(5);
					$this->response['data'] = null;
					$this->response['logout'] = 1;
					$this->response['unread_notification'] = 0;
					$this->response['isPlanExpire'] = true;
					$this->response['isSubscriptionExpire'] = false;
				}
			}/*else{
				$this->response['status'] = 403;
				$this->response['message'] = ResponseMessages::getStatusCodeMessages(5);
				$this->response['data'] = null;
				$this->response['logout'] = 1;
				$this->response['unread_notification'] = 0;
				$this->response['isPlanExpire'] = true;
			}*/
			
		/*}else{
			$this->response['unread_notification'] = 0;
		}*/
		$this->response['logout'] = 0;
		$this->response['unread_notification'] = 0;
		$this->response['isPlanExpire'] = true;
		$this->response['isSubscriptionExpire'] = false;
		//if ($this->response['status'] == '200') {
			echo json_encode($this->response);
		//}else{
		//	$status=$this->response['status'];
		//	return response()->json($this->response, $status);
		//}
	}

















































	public function pushNotificationSendActiveLogout($user, $push) {
		try
		{
          	$sound = 'true';
          	$alert = 'true';
	        /*if ($user->sound == 'Yes') {
	            $sound = 'true';
	        }
          	if ($user->alert == 'Yes') {
              	$alert = 'true';
          	}*/
          	$headtitle = ucfirst($push['title']);
		    $extramessage = ucfirst($push['description']);
          	if (isset($user->devices)) {
          		foreach ($user->devices as $k => $v) {
	          		$device_type = isset($v) && !empty($v->device_type) ? $v->device_type : 'android' ;
		          	$apptoken = isset($v) && !empty($v->device_token) ? $v->device_token : '' ;
		          	
					if ($device_type == 'android') {
		              	$this->androidPushNotificationLogout($apptoken, $headtitle, $extramessage, $sound, $alert);
		          	}
		          	if ($device_type == 'ios') {
		              	$this->sendIosNotificationLogout($apptoken, $headtitle, $extramessage, $sound, $alert);
		          	}
	          	}
          	}
			return [];
		} catch (\Exception $ex) {
			
			return [];
		}
	} 
	public function updateDevice(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("user_id", "shop_id", "device_id", "device_token", "device_type", "device_sdk", "device_manufacture", "device_brand", "device_user", "device_base", "device_incremental", "device_board", "device_host", "device_finger", "device_version", "device_name"));
		try {
			if ($user = User::select('id', 'first_name', 'last_name', 'bio', 'name', 'phone_code', 'mobile', 'email', 'gender', 'intersted', 'height', 'ethnicity', 'children', 'eduction_qualification', 'religious', 'zodiac', 'drinking', 'smoke', 'address', 'city', 'state', 'country', 'lat', 'lng', 'notification', 'email_alert', 'looking_for', 'drug', 'gender_toggle', 'intersted_toggle', 'looking_for_toggle', 'drug_toggle', 'ethnicity_toggle', 'height_toggle', 'children_toggle', 'eduction_qualification_toggle', 'religious_toggle', 'profile_visible', DB::raw("DATE_FORMAT(date_of_birth,'%b %d') as dob"))->where('id', $request->user_id)->first()) {
				if (($request->device_type == 'android') || ($request->device_type == 'ios')) {
					$this->updateUserDeviceDetails($user->id, $request->device_id, $request->device_token, $request->device_type, $request->device_sdk, $request->device_manufacture, $request->device_brand, $request->device_user, $request->device_base, $request->device_incremental, $request->device_board, $request->device_host, $request->device_finger, $request->device_version, $request->device_name);
					$this->response = array(
						"status" => 200,
						"message" => '',
						"data" => null,
					);
				}else{
					$this->response = array(
						"status" => 515,
						"message" => ResponseMessages::getStatusCodeMessages(515),
						"data" => null,
					);
				}
			} else {
				$this->response = array(
					"status" => 403,
					"message" => ResponseMessages::getStatusCodeMessages(5),
					"data" => null,
					"logout" => 1,
				);
			}
		} catch (\Exception $ex) {
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}


    public function otpSend($number,$body)
    {
    	$mobiles = urlencode($number);
		//$sms = urlencode($body);
		$sms = $body;

		$sendSms = ['message' => $sms];
		$token = env('TOKAN_FIRST');
		$headers = array();
		$headers[] = 'accept: application/json';
		$headers[] = 'authorization: Basic '.$token;
		$headers[] = 'content-type: application/json';
		$curl = curl_init();


		$smsurl = "https://us-1.dailystory.com/api/v1/textmessage/sendsingle?mobile=".$mobiles."&dsid=DailyStory%20unique%20id";
		//echo '<pre>'; print_r($smsurl); die;
		curl_setopt_array($curl, [
		  CURLOPT_URL => $smsurl,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($sendSms),
		  CURLOPT_HTTPHEADER => $headers,
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		 // echo "cURL Error #:" . $err;
		} else {
		 // echo $response;
		}
		return json_decode($response,true);
	   /* $ID = env('TWILIO_ACCOUNT_SID');
	    $token = env('TWILIO_AUTH_TOKEN');
	    $service = env('TWILIO_SMS_SERVICE_ID');
	    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $ID . '/Messages.json';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

	    curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD,$ID . ':' . $token);

	    curl_setopt($ch, CURLOPT_POST,true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,
	        'To=' . rawurlencode('+' . $number) .
	        '&MessagingServiceSid=' . $service .
	        '&From=' . rawurlencode('+1 469 798 7898') .
	        '&Body=' . rawurlencode($body));

	    $resp = curl_exec($ch);
	    curl_close($ch);
	    return json_decode($resp,true);*/


       /*	$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
        if ($output === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $output;*/
    }

    public function androidPushNotificationLogout($token, $title, $extramessage, $sound, $alert)
    {

        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

        $notification = [
            'title' => $title,
            'sound' => $sound,
            'body' => $extramessage,
            'vibrate' => $alert,
        ];
        
        $extraNotificationData = ["message" => $notification, "moredata" => $extramessage, 'type' => 'logout'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, 
            'to'        => $token, 
            'notification' => $notification,
            'data' => $extraNotificationData
        ];
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
        $data = json_encode($fcmNotification);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			        curl_setopt($ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        if ($result === FALSE) {
           // die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

	public function sendIosNotificationLogout($token, $title, $extramessage, $sound, $alert)
	{
	    $url = "https://fcm.googleapis.com/fcm/send";
	    $registrationIds = $token;
	    $serverKey =env('FCM_LEGACY_KEY');
	    $body = $extramessage;
	    $notification = array('title' =>$title , 'body' => $body, 'text' => $body, 'sound' => $sound, 'logout' => $logout);

	    $arrayToSend = array('to' => $registrationIds, 'notification'=>$notification,'priority'=>'high');
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch,CURLOPT_POST, true );
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    $result = curl_exec($ch);
	    if ($result === FALSE) 
	    {
	      //  die('FCM Send Error: ' . curl_error($ch));
	    }
	    curl_close( $ch );
	    return $result;
	}
	public function checkUser($userId, $device_id) {
		if ($user = User::where("id", $userId)->first()) {
			if ($user->status == "active") {
				if ($user->is_verified == '1') {
					if ($user->role == 'shop') {
						return true;
					} else {
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(403),
							"data" => null,
							"logout" => 1,
						);

					}
					$count = UserDevice::where('user_id', $userId)->where('device_id', $device_id)->where('status', 'active')->count();
					if ($count <= 0) {
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(240),
							"data" => null,
							"logout" => 1,
						);

					}
				}else{
					$currenttime = date('Y-m-d H:i:s');
	            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
					if ($user->is_verified == 0) {
						$otp = new UserOTP;
						$otp->user_id = $user->id;
						$otp->email = $user->email;
						$otp->otp_expire_time = $otp_expire_time;
						$verify_code = Helper::generateCode();
						$otp->code = $verify_code;
						if ($otp->save()) {
							$user->verify_code = $otp->code;
							//Notify::sendMail("emails.resent_otp", $user->toArray(), "Laravel - Verify Otp");
							$this->response = array(
								"status" => 999,
								"timer" => 60,
								"message" => 'User not verify',
							);

						} else {
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(102),
								"data" => null,
							);
						}
					} else {
						$this->response = array(
							"status" => 105,
							"message" => ResponseMessages::getStatusCodeMessages(105),
							"data" => null,
						);
					}
				}
			} else { 
				if ($user->status == 'inactive') {
					$this->response = array(
						"status" => 666,
						"message" => ResponseMessages::getStatusCodeMessages(216),
						"data" => null,
						
					);
				} elseif ($user->status == 'delete') {
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(217),
						"data" => null,
						"logout" => 1,
					);
				}
			}
		} else {
			$this->response = array(
				"status" => 403,
				"message" => ResponseMessages::getStatusCodeMessages(5),
				"data" => null,
				"logout" => 1,
			);
		}
		$this->shut_down();
			exit;
	}
	function dateDifference($start_date, $end_date)
	{
	    // calulating the difference in timestamps 
	    $diff = strtotime($start_date) - strtotime($end_date);
	     
	    // 1 day = 24 hours 
	    // 24 * 60 * 60 = 86400 seconds
	    return ceil(abs($diff));
	}


}
