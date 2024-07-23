<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Library\Helper;
use App\Library\Notify;
use App\Library\ResponseMessages;
use App\User;
use App\UserOTP;
use App\UserDevice;
use App\UserPlan;
use App\Dispensary;
use App\Product;
use App\Feedback;
use App\BusRuleRef;
use App\Support;
use App\ProductFavourite;
use App\Category;
use App\Brand;
use App\Plan;
use App\Strain;
use App\State;
use App\ProductType;
use App\Doctor;
use Auth;
use Config;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Session;
use URL;
use Image;
use File;
use PDF;
use Carbon\Carbon;

class ApiController extends Controller {


	protected function guard() {
		return auth()->guard('web');
	}

	// UNAUTHORIZED ACCESS
	public function appLogin()
	{
		$this->response = array(
			"status" => 403,
			"message" => ResponseMessages::getStatusCodeMessages(214),
			"data" => null,
			"access_token" => ''
		);
	}
	// function called to login
	public function login(Request $request) {
		$this->checkKeys(array_keys($request->all()), array('phone_code', "mobile", "type", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (($request->type == 'email') || ($request->type == 'phone')) {
					if ($request->type == 'phone') {
						$rules = array(
							'mobile' => 'required',
							'phone_code' => 'required',
						);
						$validate = Validator($request->all(), $rules);
						$attr = [
							'mobile' => 'Phone Number',
							'phone_code' => 'Phone Code',
						];	
						$validate->setAttributeNames($attr);

						if ($validate->fails()) {
							$errors = $validate->errors();
							$this->response = array(
								"status" => 300,
								"message" => $errors->first(),
								"data" => null,
								"errors" => $errors,
							);
						} else {
							if (!User::where("mobile", $request->mobile)->where('status', '!=', 'delete')->first()) {
								/*$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(108),
									"data" => null,
								);*/

								$user = new User;
								$user->phone_code = $request->phone_code;
								$user->name = 'New User';
								$user->mobile = $request->mobile;
								$user->status = 'active';
								$user->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
								if ($user->save()) {
									$currenttime = date('Y-m-d H:i:s');
					            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
									$otp = new UserOTP;
									$otp->user_id = $user->id;
									$otp->email = $user->email;
									$otp->otp_expire_time = $otp_expire_time;
									$verify_code = Helper::generateCode();
									//$verify_code = 1234;
									$otp->code = $verify_code;
									if ($otp->save()) {
										$password = Hash::make($verify_code);
										$user->is_verified = '0';
										$user->password = $password;
										$user->save();
										$mobiles = $user->phone_code.$user->mobile;
										$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";
										$sms = $otp_message;
										$this->otpSend($mobiles,$sms);
										$data = array();
										$data['name'] = $user->name;
										$data['email'] = $user->email;
										$data['verify_code'] = $verify_code;
										//Notify::sendMail("emails.user_registration", $data, "Laravel - SignUp Verification");
										$this->response = array(
											"status" => 200,
											"timer" => 60,
											"message" => ResponseMessages::getStatusCodeMessages(11),
											"data" => null,
											"mobile" => $user->mobile,
											"otp" => $verify_code,
										);
									} else {
										$this->response = array(
											"status" => 300,
											"timer" => 60,
											"message" => ResponseMessages::getStatusCodeMessages(221),
											"data" => null,
											"mobile" => '',
											"otp" => '',
										);
									}
								} else {
									$this->response = array(
										"status" => 300,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(102),
										"data" => null,
										"mobile" => '',
										"otp" => '',
									);
								}
							} else {
								if ($user = User::where("mobile", $request->mobile)->where('status', '!=', 'delete')->first()) {
									$user->phone_code = $request->phone_code;
									
									$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
									$data = $user;
									$mobile_array = array('8888888888');
									
									if (in_array($user->mobile, $mobile_array)) {
										$verify_code = '1234';
									}else{
										$verify_code = Helper::generateCode();
										//$verify_code = '1234';
										$mobiles = $user->phone_code.$user->mobile;
										$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";
										$sms = $otp_message;
										$this->otpSend($mobiles,$sms);
									}
									$currenttime = date('Y-m-d H:i:s');
			            			$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
			            			
									$otp = new UserOTP;
									$otp->user_id = $user->id;
									$otp->email = $user->email;  
									$otp->code = $verify_code;
									$otp->otp_expire_time = $otp_expire_time;
									$otp->save();
									$password = Hash::make($verify_code);
									$user->is_verified = '0';
									$user->password = $password;
									$user->is_verified = '0';
									$user->save();
									$data = array();
									$data['name'] = $user->name;
									$data['email'] = $user->email;
									$data['verify_code'] = $verify_code;
									//Notify::sendMail("emails.user_registration", $data, "Laravel - SignIn Verification");
									
									$this->response = array(
										"status" => 200,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(107),
										"data" => null,
										"mobile" => $user->mobile,
										"otp" => $verify_code,
									);
								}else{
									$this->response = array(
										"status" => 300,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(553),
										"data" => null,
										"mobile" => '',
										"otp" => '',
									);
								}
							}
						}
					}else{
						$rules = array(
							'mobile' => 'required',
						);
						$validate = Validator($request->all(), $rules);
						$attr = [
							'mobile' => 'Email',
						];	
						$validate->setAttributeNames($attr);

						if ($validate->fails()) {
							$errors = $validate->errors();
							$this->response = array(
								"status" => 300,
								"message" => $errors->first(),
								"data" => null,
								"errors" => $errors,
							);
						} else {
							if (!User::where("email", $request->mobile)->first()) {
								/*$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(108),
									"data" => null,
								);*/
								$user = new User;
								$user->phone_code = $request->phone_code;
								$user->mobile = $request->mobile;
								$user->name = 'New User';
								$user->email = $request->email;
								$user->status = 'active';
								$user->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
								if ($user->save()) {
									$currenttime = date('Y-m-d H:i:s');
					            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
									$otp = new UserOTP;
									$otp->user_id = $user->id;
									$otp->email = $user->email;
									$otp->otp_expire_time = $otp_expire_time;
									$verify_code = Helper::generateCode();
									//$verify_code = 1234;
									$otp->code = $verify_code;
									if ($otp->save()) {
										$password = Hash::make($verify_code);
										$user->is_verified = '0';
										$user->password = $password;
										
										$user->save();
										$mobiles = $user->phone_code.$user->mobile;
										$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";
										$sms = $otp_message;
										$this->otpSend($mobiles,$sms);
										$data = array();
										$data['name'] = $user->name;
										$data['email'] = $user->email;
										$data['verify_code'] = $verify_code;
										//Notify::sendMail("emails.user_registration", $data, "Laravel - SignUp Verification");
										$this->response = array(
											"status" => 200,
											"timer" => 60,
											"message" => ResponseMessages::getStatusCodeMessages(11),
											"data" => null,
											"mobile" => $user->mobile,
										);
									} else {
										$this->response = array(
											"status" => 300,
											"timer" => 60,
											"message" => ResponseMessages::getStatusCodeMessages(221),
											"data" => null,
											"mobile" => '',
										);
									}
								} else {
									$this->response = array(
										"status" => 300,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(102),
										"data" => null,
										"mobile" => '',
									);
								}
							} else {
								if ($user = User::where("email", $request->mobile)->where('status', '!=', 'out')->first()) {
									
									$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
									$data = $user;
									$verify_code = '1234';
									//$verify_code = Helper::generateCode();
										
									//Notify::sendMail("emails.user_registration", $data->toArray(), "Laravel - SignUp Verification");
									$currenttime = date('Y-m-d H:i:s');
			            			$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
			            			$data['verify_code'] = $verify_code;
									$otp = new UserOTP;
									$otp->user_id = $user->id;
									$otp->email = $user->email;  
									$otp->code = $verify_code;
									$otp->otp_expire_time = $otp_expire_time;
									$otp->save();
									
									$password = Hash::make($verify_code);
									$user->is_verified = '0';
									$user->password = $password;
									$user->is_verified = '0';
									$user->save();
									$this->response = array(
										"status" => 200,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(109),
										"data" => null,
										"mobile" => $user->email,
									);
								}else{
									$this->response = array(
										"status" => 300,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(108),
										"data" => null,
										"mobile" => '',
									);
								}
							}
						}
					}
				}else{
					$this->response = array(
						"status" => 300,
						"timer" => 60,
						"message" => ResponseMessages::getStatusCodeMessages(554),
						"data" => null,
						"mobile" => '',
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"timer" => 60,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
					"mobile" => '',
				);
			}
		} catch (\Exception $ex) {
			$this->response = array(
				"status" => 501,
				"timer" => 60,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
				"mobile" => '',
			);
		}
		$this->shut_down($request);
			exit;
	}
	// function called to signUp
	public function signUp(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("name", "phone_code", "mobile", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				$rules = array(
					'name' => 'required',
					'phone_code' => 'required',
					'mobile' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'name' => 'Name',
					'phone_code' => 'Phone Code',
					'mobile' => 'Phone Number',
				];	
				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					$errors = $validate->errors();
					$this->response = array(
						"status" => 300,
						"message" => $errors->first(),
						"data" => null,
						"errors" => $errors,
					);
				} else {
					if (!User::where("mobile", $request->mobile)->where('status', '!=', 'delete')->first()) {
							$user = new User;
							$user->phone_code = $request->phone_code;
							$user->mobile = $request->mobile;
							$user->name = $request->name;
							$user->status = 'active';
							$user->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
							if ($user->save()) {
								$currenttime = date('Y-m-d H:i:s');
				            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
								$otp = new UserOTP;
								$otp->user_id = $user->id;
								$otp->email = $user->email;
								$otp->otp_expire_time = $otp_expire_time;
								$verify_code = Helper::generateCode();
								//$verify_code = 1234;
								$otp->code = $verify_code;
								if ($otp->save()) {
									$user->is_verified = '0';
									$user->save(); 
									$mobiles = $user->phone_code.$user->mobile;
									$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";
									$sms = $otp_message;
									$this->otpSend($mobiles,$sms);
									$data = array();
									$data['name'] = $user->name;
									$data['email'] = $user->email;
									$data['verify_code'] = $verify_code;
									//Notify::sendMail("emails.user_registration", $data, "Laravel - SignUp Verification");
									$this->response = array(
										"status" => 200,
										"timer" => 60,
										"message" => ResponseMessages::getStatusCodeMessages(11),
										"data" => null,
										"mobile" => $user->mobile,
									);
								} else {
									$this->response = array(
										"status" => 300,
										"message" => ResponseMessages::getStatusCodeMessages(221),
										"data" => null,
										"mobile" => '',
									);
								}
							} else {
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(102),
									"data" => null,
									"mobile" => '',
								);
							}
					} else {
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(2),
							"data" => null,
							"mobile" => '',
						);
					}
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
					"mobile" => '',
				);
			}
		} catch (\Exception $ex) {
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
				"mobile" => '',
			);
		}
		$this->shut_down($request);
			exit;
	}
	public function resendOTP(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("mobile", "type", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (($request->type == 'email') || ($request->type == 'phone')) {
					if ($user = User::where('mobile', $request->mobile)->where('status', '!=', 'delete')->first()) {
						//$this->checkUserActive($user->id, $request->device_id);
						$currenttime = date('Y-m-d H:i:s');
		            	$otp_expire_time = date("Y-m-d H:i:s",strtotime("+1 minutes", strtotime($currenttime)));
						$otp = new UserOTP;
						$otp->user_id = $user->id;
						$otp->email = $user->email;
						$otp->otp_expire_time = $otp_expire_time;
						$verify_code = Helper::generateCode();
						//$verify_code = 1234;
						$otp->code = $verify_code;
						if ($otp->save()) {
							$password = Hash::make($verify_code);
							$user->is_verified = '0';
							$user->password = $password;
							$user->save(); 
							if ($request->type == 'phone') {
								$mobiles = $user->phone_code.$user->mobile;
								$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";
								$sms = $otp_message;
								$this->otpSend($mobiles,$sms);
								$this->response = array(
									"status" => 200,
									"timer" => 60,
									"message" => ResponseMessages::getStatusCodeMessages(11),
									"data" => null,
									"mobile" => $user->mobile,
									"otp" => $verify_code
								);
							}else{
								$this->response = array(
									"status" => 200,
									"timer" => 60,
									"message" => ResponseMessages::getStatusCodeMessages(12),
									"data" => null,
									"mobile" => $user->mobile,
									"otp" => ''
								);
							}
						} else {
							$this->response = array(
								"status" => 300,
								"timer" => 60,
								"message" => ResponseMessages::getStatusCodeMessages(102),
								"data" => null,
								"mobile" => '',
								"otp" => ''
							);
						}
					} else {
						$this->response = array(
							"status" => 300,
							"timer" => 60,
							"message" => ResponseMessages::getStatusCodeMessages(103),
							"data" => null,
							"mobile" => '',
							"otp" => ''
						);
					}
				}else{
					$this->response = array(
						"status" => 300,
						"timer" => 60,
						"message" => ResponseMessages::getStatusCodeMessages(554),
						"data" => null,
						"mobile" => '',
						"otp" => ''
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"timer" => 60,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
					"mobile" => '',
					"otp" => ''
				);
			}
		} catch (\Exception $ex) {
			$this->response = array(
				"status" => 501,
				"timer" => 60,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
				"mobile" => '',
				"otp" => ''
			);
		}
		$this->shut_down($request);
			exit;
	}
	public function verifyUser(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("mobile", "type", "code", "device_id", "device_token", "device_type"));
		$access_token = '';
		try {
			if (($request->type == 'social') || ($request->type == 'login')) {
				if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
					if ($user = User::where('mobile', $request->mobile)->where('status', 'active')->first()) {
						$otp = UserOTP::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
						if (isset($otp->id)) {
							$currenttime = date('Y-m-d H:i:s');
							if (strtotime($currenttime) <= strtotime($otp->otp_expire_time)) {
								if ($otp->code == strtoupper($request->code)) {
									$pass = $request->code.$user->id;
									$password = Hash::make($request->code);
									$user->is_verified = '1';
									$user->status = 'active';
									$user->password = $password;
									if ($user->save()) {
										// check email or password exist
										//dd(auth()->attempt(["mobile" => $request->mobile, "password" => $request->code]));
										if (Auth::attempt(["mobile" => $request->mobile, "password" => $otp->code, "status" => 'active'])) {
											$access_token = auth()->user()->createToken('Token')->accessToken;
											$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
											if ($request->type == 'login') {
												$user = User::select('id', DB::raw("CONCAT('" . URL::asset("img/avatars") . "/', avatar) image"), 'name', 'phone_code', 'mobile', 'email', 'notification', 'email_alert', 'subscription_id', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('mobile', $request->mobile)->where('status', 'active')->first();
												if (!empty($user)) {
													
													$notification_count = 0;
													$des = 'Logged in successfully';
													$push = array('sender_id' => 1, 'notification_type' => 'login', 'notification_count' => $notification_count, 'title' => 'Laravel Login', 'description' => $des);
										 		//	$this->pushNotificationSendActive($user, $push);
							                		$resultes = UserPlan::select('id', 'title', 'amount', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"), 'duration_month', 'duration_text')->where('user_id', $user->id)->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('status', 'active')->first();
													if ($resultes) {
									                	$user->plan = $resultes;
									                }else{
									                	$user->plan = array('id' => 0, 'title' => '', 'amount' => '0', 'plan_expire_time' => '', 'duration_month' => 0, 'duration_text' => '');
									                }
										 		    $updateData = array('user_id' => $user->id, 'device_id' => 'NULL');
								                    ProductFavourite::where('device_id', $request->device_id)->where('user_id', 0)->update($updateData);
													$this->response = array(
														"status" => 200,
														"message" => ResponseMessages::getStatusCodeMessages(3),
														"data" => !empty($user) ? $user : null,
														"type" => 'login',
														"access_token" => $access_token,
													);
												}else{
													$this->response = array(
														"status" => 300,
														"message" => ResponseMessages::getStatusCodeMessages(520),
														"data" => null,
														"type" => 'login',
														"access_token" => $access_token
													);
												}
											}else{
												$this->response = array(
													"status" => 200,
													"message" => ResponseMessages::getStatusCodeMessages(3),
													"data" => null,
													"type" => 'social',
													"access_token" => $access_token
												);
											}
										} else {
											$this->response = array(
												"status" => 403,
												"message" => ResponseMessages::getStatusCodeMessages(214),
												"data" => null,
												"access_token" => $access_token
											);
										}
									} else {
										$this->response = array(
											"status" => 300,
											"message" => ResponseMessages::getStatusCodeMessages(104),
											"data" => null,
											"type" => 'login',
											"access_token" => $access_token
										);
									}
								} else { 
									$this->response = array(
										"status" => 300,
										"message" => ResponseMessages::getStatusCodeMessages(102),
										"data" => null,
										"type" => 'login',
										"access_token" => $access_token
									);
								}
							}else{ 
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(516),
									"data" => null,
									"type" => 'login',
									"access_token" => $access_token
								);
							}
						} else {
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(103),
								"data" => null,
								"type" => 'login',
								"access_token" => $access_token
							);
						}
					} else {
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(5),
							"data" => null,
							"type" => 'login',
							"access_token" => $access_token
						);
					}
				}else{
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(515),
						"data" => null,
						"type" => 'login',
						"access_token" => $access_token
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(544),
					"data" => null,
					"type" => 'login',
					"access_token" => $access_token
				);
			}
		} catch (\Exception $ex) {
			
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
				"type" => 'login',
				"access_token" => $access_token
			);
		}
		$this->shut_down($request);
			exit;
	}
	public function socialLogin(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("email", "type", "social_id", "device_id", "device_token", "device_type"));
		try {
			$rules = array(
				//'mobile' => 'required',
				'email' => 'required',
				'type' => 'required',
				'social_id' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				//'mobile' => 'Phone Number',
				'email' => 'Email',
				'type' => 'Type',
				'social_id' => 'Social Id',
			];	
			$validate->setAttributeNames($attr);
			if ($validate->fails()) {
				$errors = $validate->errors();
				$this->response = array(
					"status" => 300,
					"message" => $errors->first(),
					"data" => null,
					"errors" => $errors,
				);
			} else {
				if (($request->device_type == 'android') || ($request->device_type == 'ios')) {
					if (($request->type == 'facebook') || ($request->type == 'google') || ($request->type == 'apple')) {
						if (!User::where("email", $request->email)->orWhere("facebook_id", $request->social_id)->orWhere("google_id", $request->social_id)->orWhere("apple_id", $request->social_id)->first()) {
							$this->response = array(
								"status" => 399,
								"message" => '',
								"data" => null,
							);
						} else {
							$user = User::where('email', $request->email)->orWhere("facebook_id", $request->social_id)->orWhere("google_id", $request->social_id)->orWhere("apple_id", $request->social_id)->first();
							$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
							if ($request->type == 'facebook') {
								$user->facebook_signin = '1';
								$user->facebook_id = $request->social_id;
							} elseif ($request->type == 'google') {
								$user->google_signin = '1';
								$user->google_id = $request->social_id;
							}else{
								$user->apple_signin = '1';
								$user->apple_id = $request->social_id;
							}
							$user->is_verified = '1';
							$user->status = 'active';
							$user->save();
							
							$user = User::select('id', DB::raw("CONCAT('" . URL::asset("img/avatars") . "/', avatar) image"), 'name', 'phone_code', 'mobile', 'email', 'notification', 'email_alert', 'subscription_id', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('id', $user->id)->first();
							if (!empty($user)) {
								$notification_count = 0;
								$des = 'Logged in successfully';
								$push = array('sender_id' => 1, 'notification_type' => 'login', 'notification_count' => $notification_count, 'title' => 'Laravel Login', 'description' => $des);
					 		//	$this->pushNotificationSendActive($user, $push);

					 			

		                		$resultes = UserPlan::select('id', 'title', 'amount', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"), 'duration_month', 'duration_text')->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('user_id', $user->id)->where('status', 'active')->first();
								if ($resultes) {
				                	
				                	$user->plan = $resultes;
				                }else{
				                	$user->plan = array('id' => 0, 'title' => '', 'amount' => '0', 'plan_expire_time' => '', 'duration_month' => 0, 'duration_text' => '');
				                }
								$this->response = array(
									"status" => 200,
									"message" => ResponseMessages::getStatusCodeMessages(7),
									"data" => !empty($user) ? $user : null
								);
							}else{
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(520),
									"data" => null,
								);
							}
						}
					}else{
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(35),
							"data" => null,
						);
					}
				}else{
					$this->response = array(
						"status" => 515,
						"message" => ResponseMessages::getStatusCodeMessages(515),
						"data" => null,
					);
				}
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

	public function socialCheck(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("email", "type", "social_id", "device_id", "device_token", "device_type"));
		try {
			$rules = array(
				//'mobile' => 'required',
				'email' => 'required',
				'type' => 'required',
				'social_id' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				//'mobile' => 'Phone Number',
				'email' => 'Email',
				'type' => 'Type',
				'social_id' => 'Social Id',
			];	
			$validate->setAttributeNames($attr);
			if ($validate->fails()) {
				$errors = $validate->errors();
				$this->response = array(
					"status" => 300,
					"message" => $errors->first(),
					"data" => null,
					"errors" => $errors,
				);
			} else {
				if (($request->device_type == 'android') || ($request->device_type == 'ios')) {
					if (($request->type == 'facebook') || ($request->type == 'google') || ($request->type == 'apple')) {
						if (!User::where("email", $request->email)->orWhere("facebook_id", $request->social_id)->orWhere("google_id", $request->social_id)->orWhere("apple_id", $request->social_id)->first()) {
							$this->response = array(
								"status" => 399,
								"message" => '',
								"data" => null,
							);
						} else {
							$this->response = array(
								"status" => 200,
								"message" => '',
								"data" => null,
							);
						}
					}else{
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(35),
							"data" => null,
						);
					}
				}else{
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(515),
						"data" => null,
					);
				}
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
	// function called to display home page sections
	public function dispensaryList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("name", "brand_id","radius", "lat", "lng", "address", "city", "state", "country", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 5000;
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->address) && !empty($request->address)) {
					$address = $request->address;
				}else{
					$address = '';
				}
				if (isset($request->city) && !empty($request->city)) {
					$city = $request->city;
				}else{
					$city = '';
				}
				if (isset($request->state) && !empty($request->state)) {
					$state = $request->state;
				}else{
					$state = '';
				}
				if (isset($request->country) && !empty($request->country)) {
					$country = $request->country;
				}else{
					$country = '';
				}
                $nearest_dispensaries_query = Dispensary::with(['brand' => function ($q) {
                		$q->select('id', 'name', 'description', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"));
					}])->whereHas('brand' , function ($q) {
                		$q->where('status', 'active');
					})->selectRaw("id, brand_id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])
                    ->where('status','active')
                    ->where('brand_id',$request->brand_id)
                    ->having("distance", "<", $radius)
                    ->orderBy("distance",'asc');
                if (!empty($name)) {
                    $nearest_dispensaries_query->where('name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
                }
                if (!empty($city)) {
                    $nearest_dispensaries_query->where('city', 'LIKE', "%{$city}%");
                }
                if (!empty($state)) {
                    $nearest_dispensaries_query->where('state', 'LIKE', "%{$state}%");
                }
                if (!empty($country)) {
                    $nearest_dispensaries_query->where('country', 'LIKE', "%{$country}%");
                }
                $nearest_dispensaries = $nearest_dispensaries_query->paginate(20);
                if (count($nearest_dispensaries) > 0) {
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display brand sections
	public function brandList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("name", "state_id","device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				if (isset($request->state_id) && !empty($request->state_id)) {
					$state_id = $request->state_id;
				}else{
					$state_id = '';
				}
                $nearest_brands_query = Brand::selectRaw("id, name, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image")
                    ->where('status','active')
                    ->orderBy("name",'asc');
                if (!empty($name)) {
                    $nearest_brands_query->where('name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
                }
                if (!empty($state_id)) {
                    $nearest_brands_query->whereRaw('FIND_IN_SET(?, state_id)', [$state_id]);
                }
                $nearest_brands = $nearest_brands_query->paginate(20);
                if (count($nearest_brands) > 0) {
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_brands) ? $nearest_brands : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display state List sections
	public function stateList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
                $nearest_states_query = State::selectRaw("id, name")
                    ->where('status','active')
                    ->where('is_allow','false')
                    ->orderBy("name",'asc');
                $nearest_states = $nearest_states_query->paginate(20);
                if (count($nearest_states) > 0) {
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_states) ? $nearest_states : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display state List sections
	public function allowLocation(Request $request) {
		try {
            $nearest_states_query = State::selectRaw("id, name")
                ->where('status','active')
                ->where('is_allow','true')
                ->orderBy("name",'asc');
            $nearest_states = $nearest_states_query->paginate(2000);
            if (count($nearest_states) > 0) {
            	$this->response = array(
					"status" => 200,
					"message" => ResponseMessages::getStatusCodeMessages(125),
					"data" => !empty($nearest_states) ? $nearest_states : null,
				);
            }else{
            	$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(520),
					"data" => null,
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
	// function called to dispensary Wise Product List sections
	public function dispensaryWiseProductList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("dispensary_id", "category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$radius = 5000;
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::with(['products' => function ($q)  use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
                		$q->with(['brand' => function ($q){
	                		$q->select("id", "name");
						}, 'category' => function ($q){
	                		$q->select("id", "name");
						}, 'type' => function ($q){
	                		$q->select("id", "name");
						}, 'strain' => function ($q){
	                		$q->select("id", "name");
						}])->whereHas('brand' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('category' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('type' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('strain' , function ($q1) {
						    $q1->where('status', 'active');
						})->select('products.id', DB::raw('CAST(if(price > discount_price && discount_price != 0,discount_price,price) AS DECIMAL(10,2)) as finalprice'), 'products.product_code', 'products.brand_id', 'products.product_sku', 'products.parent_id', 'products.sub_parent_id', 'products.dispensary_id', 'products.strain_id', 'products.sub_strain_id', 'products.type_id', 'products.amount', 'products.thc', 'products.cbd', 'products.name', 'products.description', 'products.price_color_code', 'products.qty', 'products.price', 'products.discount_price', 'products.manage_stock', 'products.is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url"), 'products.image_url as image', 'products.product_url', 'products.updated_at as created_date');
	                	if (!empty($name)) {
		                   /* $q->where('products.name', 'LIKE', "%$name%");
		                    $q->where('companies.name', 'LIKE', "%$name%");*/
		                    $q->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
		                }
		                if (!empty($category_id)) {
		                    $q->where('products.parent_id', $category_id);
		                }
		                if (!empty($type_id)) {
		                    $q->where('products.type_id', $type_id);
		                }
		                if (!empty($strain_id)) {
		                    $q->where('products.strain_id', $strain_id);
		                }
		                $q->orderBy('products.manage_stock', 'desc');
		                if ($sort_by == 'price') {
	                    	//$q->orderByRaw('CONVERT(products.price, SIGNED) asc');
	                    	$q->orderBy('finalprice', 'ASC');
	                    }else if($sort_by == 'thc'){
	                    	$q->orderByRaw('CONVERT(products.thc, SIGNED) desc');
	                    }else{
	                    	//$q->orderBy('distance', 'asc');
	                    }
						$q->where('products.status', 'active');
						if ($amount != '') {
							$q->where('products.amount', $amount);
						}
						$q->join('companies', 'products.brand_id', '=', 'companies.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->offset($page_no * 100)->limit(100)->get();
						
					}])->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->having("distance", "<", $radius)
                    ->where('status','active');
                    if ($sort_by == 'distance') {
                    	$nearest_dispensaries_query->orderBy('distance', 'asc');
                    }
                    $nearest_dispensaries_query->where('id', $request->dispensary_id);
                $nearest_dispensaries = $nearest_dispensaries_query->first();
                if ($nearest_dispensaries) {
                	//$str = str_split($nearest_dispensaries->address, 25);
                	//$nearest_dispensaries->address = implode("\n", $str);
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}

	// function called to dispensary Wise Product List sections
	public function dispensaryWiseProductListHome(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("dispensary_id", "category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$radius = 5000;
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
						$category_id = $catCheck->id;
					}else{
						$category_id = 0;
					}
					
				}
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::with(['products' => function ($q)  use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
                		$q->with(['brand' => function ($q){
	                		$q->select("id", "name");
						}, 'category' => function ($q){
	                		$q->select("id", "name");
						}, 'type' => function ($q){
	                		$q->select("id", "name");
						}, 'strain' => function ($q){
	                		$q->select("id", "name");
						}])->whereHas('brand' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('category' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('type' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('strain' , function ($q1) {
						    $q1->where('status', 'active');
						})->select('products.id', DB::raw('CAST(if(price > discount_price && discount_price != 0,discount_price,price) AS DECIMAL(10,2)) as finalprice'), 'products.product_code', 'products.brand_id', 'products.product_sku', 'products.parent_id', 'products.sub_parent_id', 'products.dispensary_id', 'products.strain_id', 'products.sub_strain_id', 'products.type_id', 'products.amount', 'products.thc', 'products.cbd', 'products.name', 'products.description', 'products.price_color_code', 'products.qty', 'products.price', 'products.discount_price', 'products.manage_stock', 'products.is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url"), 'products.image_url as image', 'products.product_url', 'products.updated_at as created_date')->where('products.manage_stock', 1);
	                	if (!empty($name)) {
		                   /* $q->where('products.name', 'LIKE', "%$name%");
		                    $q->where('companies.name', 'LIKE', "%$name%");*/
		                    $q->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')
			                   ->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
		                }
		                if (!empty($category_id)) {
		                    $q->where('products.parent_id', $category_id);
		                }
		                if (!empty($type_id)) {
		                    $q->where('products.type_id', $type_id);
		                }
		                if (!empty($strain_id)) {
		                    $q->where('products.strain_id', $strain_id);
		                }
		                $q->orderBy('products.manage_stock', 'desc');
		                if ($sort_by == 'price') {
		                    $q->orderBy('finalprice', 'ASC');
	                    //	$q->orderByRaw('CONVERT(products.price, SIGNED) asc');
	                    }else if($sort_by == 'thc'){
	                    	$q->orderByRaw('CONVERT(products.thc, SIGNED) desc');
	                    }else{
	                    	//$q->orderBy('distance', 'asc');
	                    }
						$q->where('products.status', 'active');
						if ($amount != '') {
							$q->where('products.amount', $amount);
						}
						$q->join('companies', 'products.brand_id', '=', 'companies.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->offset($page_no * 100)->limit(100)->get();
						
					}])->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->having("distance", "<", $radius)
                    ->where('status','active');
                    if ($sort_by == 'distance') {
                    	$nearest_dispensaries_query->orderBy('distance', 'asc');
                    }
                    $nearest_dispensaries_query->where('id', $request->dispensary_id);
                $nearest_dispensaries = $nearest_dispensaries_query->first();
                if ($nearest_dispensaries) {
                	//$str = str_split($nearest_dispensaries->address, 25);
                	//$nearest_dispensaries->address = implode("\n", $str);
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}

	// function called to display home page sections
	public function homePageData(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("radius", "category_id", "name","lat", "lng", "type_id", "strain_id", "sort_by", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
			    
			    /*if ($this->getAddress($request->lat,$request->lng) == 1) {
					
				}else{
					$this->response = array(
						"status" => 777,
						"message" => 'You are not allowed this location using Laravel app!',
						"data" => null,
					);
					$this->shut_down();
					exit;
				}*/
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				$amount = '';
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
                $nearest_dispensaries_query = Product::with(['dispensary' => function ($q)  use ($lat, $lng, $radius, $sort_by) {
                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
					}, 'brand' => function ($q){
                		$q->select("id", "name");
					}, 'category' => function ($q){
                		$q->select("id", "name");
					}, 'type' => function ($q){
                		$q->select("id", "name");
					}, 'strain' => function ($q){
                		$q->select("id", "name");
					}])->whereHas('brand' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('category' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('type' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('strain' , function ($q) {
		                $q->where('status', 'active');
					})->selectRaw("products.id, CAST(if(price > discount_price && discount_price != 0,discount_price,price) AS DECIMAL(10,2)) as finalprice, products.product_code, products.brand_id, products.product_sku, products.parent_id, products.sub_parent_id, products.dispensary_id, products.strain_id, products.sub_strain_id, products.type_id, products.amount, products.thc, products.cbd, products.name, products.description, products.price_color_code, products.qty, products.price, products.discount_price, products.manage_stock, products.is_featured, CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url, products.image_url as image, products.product_url, products.updated_at as created_date, ( 3959 * acos( cos( radians(?) ) *
	                       cos( radians( lat ) )
	                       * cos( radians( lng ) - radians(?)
	                       ) + sin( radians(?) ) *
	                       sin( radians( lat ) ) )
	                     ) AS distance", [$lat, $lng, $lat])->where('products.status','active');
					$nearest_dispensaries_query->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'products.brand_id', '=', 'companies.id')
						->where('dispensaries.status', 'active')
						->where('companies.status', 'active')
						->having("distance", "<", $radius)
						->whereNotNull('lat')
						->whereNotNull('lng');
					$nearest_dispensaries_query->orderBy('manage_stock', 'desc');
                	if ($category_id != 0) {
	                    $nearest_dispensaries_query->where('products.parent_id', $category_id);
	                }
	                if ($type_id != 0) {
	                    $nearest_dispensaries_query->where('products.type_id', $type_id);
	                }
	                if ($strain_id != 0) {
	                    $nearest_dispensaries_query->where('products.strain_id', $strain_id);
	                }
                    if ($amount != '') {
						$nearest_dispensaries_query->where("products.amount", $amount);
                    }
                    if ($name != '') {
	                    //$nearest_dispensaries_query->where('products.name', 'LIKE', "%$name%")->where('companies.name', 'LIKE', "%$name%");
	                    /*$nearest_dispensaries_query->where('products.name', 'LIKE', "%{$name}%");
	                    $nearest_dispensaries_query->orWhere('companies.name', 'LIKE', "%{$name}%");*/
	                    $nearest_dispensaries_query->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')
			                   ->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
	                    //$nearest_dispensaries_query->where(DB::raw('LOWER(products.name)'), 'LIKE', '%'.strtolower($name).'%')->orWhere(DB::raw('LOWER(companies.name)'), 'LIKE', '%'.strtolower($name).'%');
	                }
	                if ($sort_by == 'price') {
                    	//$nearest_dispensaries_query->orderBy('products.price', 'asc');
                    	//$nearest_dispensaries_query->orderByRaw('CONVERT(products.price, SIGNED) asc');
                    	//$nearest_dispensaries_query->orderByRaw('COALESCE(products.discount_price, products.price) DESC');
                    	//$nearest_dispensaries_query->orderBy(DB::raw("IF(discount_price<0, price, discount_price)"), request('price', 'ASC'));
                    	//$nearest_dispensaries_query->orderByRaw('IF(discount_price>0,products.discount_price, products.price) ASC');
                    	$nearest_dispensaries_query->orderBy('finalprice', 'ASC');
                    }else if($sort_by == 'thc'){
                    	$nearest_dispensaries_query->orderByRaw('CONVERT(products.thc, SIGNED) desc');
                    }else{
                    	$nearest_dispensaries_query->orderBy('distance', 'asc');
                    }
                	$nearest_dispensaries = $nearest_dispensaries_query->where('products.manage_stock', 1)->paginate(30);

                if (count($nearest_dispensaries) > 0) {
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{

                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			//echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}


	// function called to display mapHomePageData sections
	public function mapHomePageData(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
				
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::join('products', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'dispensaries.brand_id', '=', 'companies.id');
                
				$nearest_dispensaries_query->selectRaw("dispensaries.id, CAST(if(price > discount_price && discount_price != 0,discount_price,price) AS DECIMAL(10,2)) as finalprice, dispensaries.brand_id, dispensaries.name, dispensaries.lat, dispensaries.lng, CONCAT('" . asset("uploads/brands") . "/', dispensaries.image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
                $nearest_dispensaries_query->having("distance", "<", $radius);
				if ($name != '') {
                    $nearest_dispensaries_query->where(function($query) use ($name){
		                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
		                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')
		                   ->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
		            });
                }
                if ($category_id != 0) {
                    $nearest_dispensaries_query->where('products.parent_id', $category_id);
                }
                if ($type_id != 0) {
                    $nearest_dispensaries_query->where('products.type_id', $type_id);
                }
                if ($strain_id != 0) {
                    $nearest_dispensaries_query->where('products.strain_id', $strain_id);
                }
                $nearest_dispensaries_query->orderBy('products.manage_stock', 'desc')->where('products.manage_stock', 1);
                if ($sort_by == 'price') {
                	//$nearest_dispensaries_query->orderByRaw('CONVERT(products.price, SIGNED) asc');
                	$nearest_dispensaries_query->orderBy('finalprice', 'ASC');
                }else if($sort_by == 'thc'){
                	$nearest_dispensaries_query->orderByRaw('CONVERT(products.thc, SIGNED) desc');
                }else{
                	$nearest_dispensaries_query->orderBy('distance', 'asc');
                }
				$nearest_dispensaries_query->where('products.status', 'active');
				if ($amount != '') {
					$nearest_dispensaries_query->where('products.amount', $amount);
				}
                
               // $nearest_dispensaries_query->groupBy('dispensaries.brand_id');
                //$nearest_dispensaries_query->groupBy('products.brand_id');
                $nearest_dispensaries_query->where('dispensaries.status','active');
                $nearest_dispensaries = $nearest_dispensaries_query->distinct('dispensaries.brand_id')->get();

                /*$nearest_dispensaries_query = Product::whereHas('brand' , function ($q) {
	                $q->where('status', 'active');
				})->whereHas('category' , function ($q) {
	                $q->where('status', 'active');
				})->whereHas('type' , function ($q) {
	                $q->where('status', 'active');
				})->whereHas('strain' , function ($q) {
	                $q->where('status', 'active');
				});
				$nearest_dispensaries_query->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'products.brand_id', '=', 'companies.id');
				$nearest_dispensaries_query->selectRaw("dispensaries.id, dispensaries.brand_id, dispensaries.name, dispensaries.lat, dispensaries.lng, CONCAT('" . asset("uploads/brands") . "/', dispensaries.image) image, ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->where('products.status','active');
				
				$nearest_dispensaries_query->where('dispensaries.status', 'active')
					->where('companies.status', 'active')
					->having("distance", "<", $radius)
					->whereNotNull('lat')
					->whereNotNull('lng');
				$nearest_dispensaries_query->orderBy('manage_stock', 'desc');
            	if ($category_id != 0) {
                    $nearest_dispensaries_query->where('products.parent_id', $category_id);
                }
                if ($type_id != 0) {
                    $nearest_dispensaries_query->where('products.type_id', $type_id);
                }
                if ($strain_id != 0) {
                    $nearest_dispensaries_query->where('products.strain_id', $strain_id);
                }
                if ($amount != '') {
					$nearest_dispensaries_query->where("products.amount", $amount);
                }
                if ($name != '') {
                    $nearest_dispensaries_query->where(function($query) use ($name){
		                    $query->where('products.name', 'LIKE', "%{$name}%")
		                   ->orWhere('companies.name', 'LIKE', "%{$name}%");
		            });
                }
                if ($sort_by == 'price') {
                	$nearest_dispensaries_query->orderByRaw('CONVERT(products.price, SIGNED) asc');
                }else if($sort_by == 'thc'){
                	$nearest_dispensaries_query->orderByRaw('CONVERT(products.thc, SIGNED) desc');
                }else{
                	$nearest_dispensaries_query->orderBy('distance', 'asc');
                }
            	$nearest_dispensaries = $nearest_dispensaries_query->where('products.manage_stock', 1);
            	$nearest_dispensaries_query->groupBy('dispensaries.brand_id');
            	$nearest_dispensaries_query->limit(200)->get();*/
                /*if (!empty($nearest_dispensaries)) {
                	foreach ($nearest_dispensaries as $key => $value) {
                		$brands_query = Brand::selectRaw("CONCAT('" . asset("uploads/brands") . "/', image) image")->where('id', $value->brand_id)->first();
                		if ($brands_query) {
                			$nearest_dispensaries[$key]['image'] = $brands_query->image;
                			
                		}
                		$nearest_dispensaries[$key]['products'] = array();
                	}
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }*/
                $resArray = array();
                if (!empty($nearest_dispensaries)) {
                	foreach ($nearest_dispensaries as $key => $value) {
                		 $brands_query = Brand::selectRaw("CONCAT('" . asset("uploads/brands") . "/', image) image")->where('id', $value->brand_id)->first();
                		if ($brands_query) {
                			$nearest_dispensaries[$key]['image'] = $brands_query->image;
                			
                		 }
                		 $nearest_dispensaries[$key]['products'] = array();
                		if (!isset($resArray[$value->brand_id])) {
                			# code...
                			$resArray[$value->brand_id]= $value;
                			
                		}
                	}
					$resArray = array_values($resArray);
                	//dd($nearest_dispensaries);
                	$this->response = array(
						"status" => !empty($resArray) ? 200 : 300,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($resArray) ? $resArray : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}
	// function called to display mapHomePageData sections
	public function mapHomePageDataOld1(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::with(['products' => function ($q)  use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
                		$q->with(['brand' => function ($q){
	                		$q->select("id", "name");
						}, 'category' => function ($q){
	                		$q->select("id", "name");
						}, 'type' => function ($q){
	                		$q->select("id", "name");
						}, 'strain' => function ($q){
	                		$q->select("id", "name"); 
						}])->whereHas('brand' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('category' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('type' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('strain' , function ($q1) {
						    $q1->where('status', 'active');
						})->select('products.id', 'products.product_code', 'products.brand_id', 'products.product_sku', 'products.parent_id', 'products.sub_parent_id', 'products.dispensary_id', 'products.strain_id', 'products.sub_strain_id', 'products.type_id', 'products.amount', 'products.thc', 'products.cbd', 'products.name', 'products.description', 'products.price_color_code', 'products.qty', 'products.price', 'products.discount_price', 'products.manage_stock', 'products.is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url"), 'products.image_url as image', 'products.product_url', 'products.updated_at as created_date')->offset($page_no * 1)->limit(0)->get();
					}])->whereHas('products' , function ($q1) use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
						   	if ($name != '') {
			                    /*$q1->where('products.name', 'LIKE', "%$name%");
			                    $q1->orWhere('companies.name', 'LIKE', "%$name%");*/
			                    $q1->where(function($query) use ($name){
					                $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
					            });
			                }
			                if ($category_id != 0) {
			                    $q1->where('products.parent_id', $category_id);
			                }
			                if ($type_id != 0) {
			                    $q1->where('products.type_id', $type_id);
			                }
			                if ($strain_id != 0) {
			                    $q1->where('products.strain_id', $strain_id);
			                }
			                $q1->orderBy('products.manage_stock', 'desc');
			                if ($sort_by == 'price') {
		                    	$q1->orderByRaw('CONVERT(products.price, SIGNED) asc');
		                    }else if($sort_by == 'thc'){
		                    	$q1->orderByRaw('CONVERT(products.thc, SIGNED) desc');
		                    }else{
		                    	$q1->orderBy('distance', 'asc');
		                    }
							$q1->where('products.status', 'active');
							if ($amount != '') {
								$q1->where('products.amount', $amount);
							}
							$q1->join('companies', 'products.brand_id', '=', 'companies.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id');
						})->selectRaw("id, brand_id, name, lat, lng, CONCAT('" . asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
						$nearest_dispensaries_query->having("distance", "<", $radius);
						$nearest_dispensaries_query->groupBy('brand_id')->where('status','active');
                    /*if (isset($request->dispensary_id) && !empty($request->dispensary_id)) {
                    	$nearest_dispensaries_query->where('id', $request->dispensary_id);
                    }*/
                $nearest_dispensaries = $nearest_dispensaries_query->limit(50)->get();
                if ($nearest_dispensaries) {
                	foreach ($nearest_dispensaries as $key => $value) {
                		$brands_query = Brand::selectRaw("CONCAT('" . asset("uploads/brands") . "/', image) image")->where('id', $value->brand_id)->first();
                		if ($brands_query) {
                			$nearest_dispensaries[$key]['image'] = $brands_query->image;
                			
                		}
                		$nearest_dispensaries[$key]['products'] = array();
                	}
                	//dd($nearest_dispensaries);
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display mapHomePageData sections
	public function mapHomePageDataBackup(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
			if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
			
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::with(['products' => function ($q)  use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
                		$q->with(['brand' => function ($q){
	                		$q->select("id", "name");
						}, 'category' => function ($q){
	                		$q->select("id", "name");
						}, 'type' => function ($q){
	                		$q->select("id", "name");
						}, 'strain' => function ($q){
	                		$q->select("id", "name"); 
						}])->whereHas('brand' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('category' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('type' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('strain' , function ($q1) {
						    $q1->where('status', 'active');
						})->select('products.id', 'products.product_code', 'products.brand_id', 'products.product_sku', 'products.parent_id', 'products.sub_parent_id', 'products.dispensary_id', 'products.strain_id', 'products.sub_strain_id', 'products.type_id', 'products.amount', 'products.thc', 'products.cbd', 'products.name', 'products.description', 'products.price_color_code', 'products.qty', 'products.price', 'products.discount_price', 'products.manage_stock', 'products.is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url"), 'products.image_url as image', 'products.product_url', 'products.updated_at as created_date')->join('companies', 'products.brand_id', '=', 'companies.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id');
	                	if (!empty($name)) {
		                   /* $q->where('products.name', 'LIKE', "%$name%");
		                    $q->where('companies.name', 'LIKE', "%$name%");*/
		                    $q->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
		                }
		                if (!empty($category_id)) {
		                    $q->where('products.parent_id', $category_id);
		                }
		                if (!empty($type_id)) {
		                    $q->where('products.type_id', $type_id);
		                }
		                if (!empty($strain_id)) {
		                    $q->where('products.strain_id', $strain_id);
		                }
		                $q->orderBy('products.manage_stock', 'desc');
		                if ($sort_by == 'price') {
	                    	$q->orderByRaw('CONVERT(products.price, SIGNED) asc');
	                    }else if($sort_by == 'thc'){
	                    	$q->orderByRaw('CONVERT(products.thc, SIGNED) desc');
	                    }else{
	                    	$q->orderBy('distance', 'asc');
	                    }
						$q->where('products.status', 'active');
						if ($amount != '') {
							$q->where('products.amount', $amount);
						}
						$q->offset($page_no * 1)->limit(1)->get();
						
					}])->whereHas('products' , function ($q1) {
						    $q1->where('status', 'active');
						})->selectRaw("id, brand_id, name, lat, lng, CONCAT('" . asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->having("distance", "<", $radius)->groupBy('brand_id')->where('status','active');
                    if (isset($request->dispensary_id) && !empty($request->dispensary_id)) {
                    	$nearest_dispensaries_query->where('id', $request->dispensary_id);
                    }
                $nearest_dispensaries = $nearest_dispensaries_query->limit(50)->get();
                if ($nearest_dispensaries) {
                	foreach ($nearest_dispensaries as $key => $value) {
                		$brands_query = Brand::selectRaw("CONCAT('" . asset("uploads/brands") . "/', image) image")->where('id', $value->brand_id)->first();
                		if ($brands_query) {
                			$nearest_dispensaries[$key]['image'] = $brands_query->image;
                		}
                	}
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display mapHomePageData sections
	public function mapHomePageDataOld(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "type_id", "strain_id", "name", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				$amount = '';
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $amount == '' && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
				if (isset($request->page) && !empty($request->page)) {
					$page_no = $request->page - 1;
				}else{
					$page_no = 0;
				}
                $nearest_dispensaries_query = Dispensary::with(['products' => function ($q)  use ($name, $category_id, $type_id, $strain_id, $amount, $page_no, $sort_by) {
                		$q->with(['brand' => function ($q){
	                		$q->select("id", "name");
						}, 'category' => function ($q){
	                		$q->select("id", "name");
						}, 'type' => function ($q){
	                		$q->select("id", "name");
						}, 'strain' => function ($q){
	                		$q->select("id", "name"); 
						}])->whereHas('brand' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('category' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('type' , function ($q1) {
						    $q1->where('status', 'active');
						})->whereHas('strain' , function ($q1) {
						    $q1->where('status', 'active');
						})->select('products.id', 'products.product_code', 'products.brand_id', 'products.product_sku', 'products.parent_id', 'products.sub_parent_id', 'products.dispensary_id', 'products.strain_id', 'products.sub_strain_id', 'products.type_id', 'products.amount', 'products.thc', 'products.cbd', 'products.name', 'products.description', 'products.price_color_code', 'products.qty', 'products.price', 'products.discount_price', 'products.manage_stock', 'products.is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', products.image) image_url"), 'products.image_url as image', 'products.product_url', 'products.updated_at as created_date')->join('companies', 'products.brand_id', '=', 'companies.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id');
	                	if (!empty($name)) {
		                    /*$q->where('products.name', 'LIKE', "%$name%");
		                    $q->where('companies.name', 'LIKE', "%$name%");*/
		                    $q->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
		                }
		                if (!empty($category_id)) {
		                    $q->where('products.parent_id', $category_id);
		                }
		                if (!empty($type_id)) {
		                    $q->where('products.type_id', $type_id);
		                }
		                if (!empty($strain_id)) {
		                    $q->where('products.strain_id', $strain_id);
		                }
		                if ($sort_by == 'price') {
	                    	$q->orderByRaw('CONVERT(products.price, SIGNED) asc');
	                    }else if($sort_by == 'thc'){
	                    	$q->orderByRaw('CONVERT(products.thc, SIGNED) desc');
	                    }else{
	                    	$q->orderBy('distance', 'asc');
	                    }
						$q->where('products.status', 'active');
						if ($amount != '') {
							$q->where('products.amount', $amount);
						}
						$q->offset($page_no * 1)->limit(1)->get();
						//$q->get(1);
						
					}])->whereHas('products' , function ($q1) {
						    $q1->where('status', 'active');
						})->selectRaw("id, name, lat, lng, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->having("distance", "<", $radius)->where('status','active');
                    if (isset($request->dispensary_id) && !empty($request->dispensary_id)) {
                    	$nearest_dispensaries_query->where('id', $request->dispensary_id);
                    }
                $nearest_dispensaries = $nearest_dispensaries_query->limit(50)->get();
                if ($nearest_dispensaries) {
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display home page sections
	public function productDetails(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("product_id", "lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				if (isset($request->product_id) && !empty($request->product_id)) {
					$product_id = $request->product_id;
				}else{
					$product_id = 0;
				}
                $nearest_dispensaries_query = Product::with(['dispensary' => function ($q)  use ($lat, $lng) {
                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
					}, 'brand' => function ($q){
                		$q->select("id", "name");
					}, 'category' => function ($q){
                		$q->select("id", "name");
					}, 'type' => function ($q){
                		$q->select("id", "name");
					}, 'strain' => function ($q){
                		$q->select("id", "name");
					}])->whereHas('dispensary' , function ($q)  use ($lat, $lng) {
                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat])->whereNotNull('lat')->whereNotNull('lng');
					})->whereHas('brand' , function ($q) {
					    $q->where('status', 'active');
					})->whereHas('category' , function ($q) {
					    $q->where('status', 'active');
					})->whereHas('type' , function ($q) {
					    $q->where('status', 'active');
					})->whereHas('strain' , function ($q) {
					    $q->where('status', 'active');
					})->select('id', 'product_code', 'brand_id', 'product_sku', 'parent_id', 'sub_parent_id', 'dispensary_id', 'strain_id', 'sub_strain_id', 'type_id', 'amount', 'thc', 'cbd', 'name', 'description', 'price_color_code', 'qty', 'price', 'discount_price', 'manage_stock', 'is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', image) image_url"), 'image_url as image', 'product_url', 'updated_at as created_date')->where('status','active')->where('id',$product_id);
                	$nearest_dispensaries = $nearest_dispensaries_query->first();
                if ($nearest_dispensaries) {
                	if(isset($user->id)){
                		$nearest_dispensaries->isFav = !empty(ProductFavourite::where('user_id', $user->id)->where('product_id', $nearest_dispensaries->id)->where('status', 'active')->first()) ? true : false;
                		$favData = ProductFavourite::select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('user_id', $user->id)->where('product_id', $nearest_dispensaries->id)->where('status', 'active')->first();
                		if (!empty($favData)) {
                			$nearest_dispensaries->favourite = $favData;
                		}else{
                			$nearest_dispensaries->favourite = array('fav_id' => 0, 'product_id' => 0, 'is_user_status' => '', 'pause_status' => '', 'pause_expire_time' => '', 'created_date' => '');
                		}
                	}else{
                		$nearest_dispensaries->isFav = !empty(ProductFavourite::where('device_id', $request->device_id)->where('product_id', $nearest_dispensaries->id)->where('status', 'active')->first()) ? true : false;
                		$favData = ProductFavourite::select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('device_id', $request->device_id)->where('product_id', $nearest_dispensaries->id)->where('status', 'active')->first();
                		if (!empty($favData)) {
                			$nearest_dispensaries->favourite = $favData;
                		}else{
                			$nearest_dispensaries->favourite = array('fav_id' => 0, 'product_id' => 0, 'is_user_status' => '', 'pause_status' => '', 'pause_expire_time' => '', 'created_date' => '');
                		}
                	}
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{
                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to feedbackStore
	public function feedbackStore(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("smiley", "category", "description", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				$rules = array(
					'smiley' => 'required',
					'category' => 'required',
					'description' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'smiley' => 'Smiley',
					'category' => 'Category',
					'description' => 'Description',
				];	
				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					$errors = $validate->errors();
					$this->response = array(
						"status" => 300,
						"message" => $errors->first(),
						"data" => null,
						"errors" => $errors,
					);
				} else {

						$Feedback = new Feedback;
						//$Feedback->user_id = 0;
						$Feedback->user_id = $request->device_id;
						$Feedback->smiley = $request->smiley;
						$Feedback->category = $request->category;
						$Feedback->description = ucfirst($request->description);
						if ($Feedback->save()) {
							$this->response = array(
								"status" => 200,
								"message" => ResponseMessages::getStatusCodeMessages(218),
								"data" => null,
							);
						} else {
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(221),
								"data" => null,
							);
						}
					
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function to mark/unmark product favourite
	public function manageUserFavourite(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("product_id", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				$rules = array(
					'product_id' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'product_id' => 'Product',
				];	
				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					$errors = $validate->errors();
					$this->response = array(
						"status" => 300,
						"message" => $errors->first(),
						"data" => null,
						"errors" => $errors,
					);
				} else {
					if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
						$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
					}
					$products = explode(",", $request->product_id);
					if (!empty($products)) {
						foreach ($products as $key => $value) {
							if ($product = Product::where('id', $value)->where('status','!=','delete')->first()) {
								if(isset($user->id)){
									$fav = ProductFavourite::where('user_id', $user->id)->where('product_id', $value)->first();
									if (isset($fav->id)) {
										$fav->status = ($fav->status == 'active') ? 'inactive' : 'active';
										$fav->is_user_status = ($fav->status == 'active') ? 'inactive' : 'inactive';
									} else {
										$fav = new ProductFavourite;
										$fav->device_id = $request->device_id;
										$fav->user_id = $request->user_id;
										$fav->product_id = $value;
										$fav->status = 'active';
										$fav->is_user_status = 'inactive';
										$fav->pause_status = 'inactive';
										$fav->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
									}
									$fav->save();
									
									$updateData2 = array('status' => $fav->status, 'is_user_status' => $fav->is_user_status, 'pause_status' => $fav->pause_status, 'pause_expire_time' => $fav->pause_expire_time);
						            ProductFavourite::where('user_id', $user->id)->where('product_id', $value)->update($updateData2);

									if ($fav->status == 'active') {
										$notification_title = 'Successfully added favourite';
										$notification_des = 'Successfully added '.$fav->product->name.' favourite';
									}else{
										$notification_title = 'Successfully remove favourite';
										$notification_des = 'Successfully remove '.$fav->product->name.' favourite';
									}
									$push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $notification_title, 'description' => $notification_des);
						 		//	$this->pushNotificationSendActive($user, $push);


								}else{
									$fav = ProductFavourite::where('device_id', $request->device_id)->where('product_id', $value)->first();
									if (isset($fav->id)) {
										$fav->status = ($fav->status == 'active') ? 'inactive' : 'active';
										$fav->is_user_status = ($fav->status == 'active') ? 'inactive' : 'inactive';
									} else {
										$fav = new ProductFavourite;
										$fav->device_id = $request->device_id;
										$fav->user_id = 0;
										$fav->product_id = $value;
										$fav->status = 'active';
										$fav->is_user_status = 'inactive';
										$fav->pause_status = 'inactive';
										$fav->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
									}
									$fav->save();
									if ($fav->status == 'active') {
										$notification_title = 'Successfully added favourite';
										$notification_des = 'Successfully added '.$fav->product->name.' favourite';
									}else{
										$notification_title = 'Successfully remove favourite';
										$notification_des = 'Successfully remove '.$fav->product->name.' favourite';
									}
									$push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $notification_title, 'description' => $notification_des);
						 		//	$this->pushNotificationSendGuestActive($request->device_type, $request->device_token, $push);
								}
							}
						}
					}		
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(523),
						"data" => null,
					);		
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to getUserFavourites
	public function getUserFavourites(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				$rules = array(
					'device_id' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'device_id' => 'Device_id',
				];	
				$validate->setAttributeNames($attr);
				if ($validate->fails()) {
					$errors = $validate->errors();
					$this->response = array(
						"status" => 300,
						"message" => $errors->first(),
						"data" => null,
						"errors" => $errors,
					);
				} else {
				   $user_fav = ProductFavourite::select('id', 'is_user_status', 'pause_status')->where('pause_expire_time', '<=' ,date('Y-m-d'))->where('is_user_status', 'pause')->where('pause_status', 'active')->get();
                    if (!empty($user_fav)) {
                        foreach ($user_fav as $key1 => $value1) {
                            ProductFavourite::where('id', $value1->id)->update(array('is_user_status' => 'active', 'pause_status' => 'inactive'));
                        }
                    }
					if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
						$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
					}
						
						//if ($user->plan_expire_time >= date('Y-m-d')) {
							if (isset($request->lat) && !empty($request->lat)) {
								$lat = $request->lat;
							}else{
								$lat = '-7.0157404';
							}
							if (isset($request->lng) && !empty($request->lng)) {
								$lng = $request->lng;
							}else{
								$lng = '110.4171283';
							} 
							if (isset($user->id)) {
								$updateData = array('user_id' => $user->id, 'device_id' => 'NULL');
								ProductFavourite::where('device_id', $request->device_id)->where('user_id', 0)->update($updateData);
								$nearest_favs_query = Product::with(['favourite' => function ($q)  use ($user) {
										$q->where('user_id', $user->id);
										$q->where('status', 'active');
										$q->select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', 'created_at as created_date');
									}])->whereHas('favourite' , function ($q)  use ($user) {
										$q->where('user_id', $user->id);
										$q->where('status', 'active')/*->whereHas('user' , function ($q_fav) {
        								    $q_fav->where('status', 'active');
        								})*/;
										$q->groupBy('product_id');
										$q->select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"));
									})->with(['dispensary' => function ($q)  use ($lat, $lng) {
			                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
			                     ( 3959 * acos( cos( radians(?) ) *
			                       cos( radians( lat ) )
			                       * cos( radians( lng ) - radians(?)
			                       ) + sin( radians(?) ) *
			                       sin( radians( lat ) ) )
			                     ) AS distance", [$lat, $lng, $lat]);
								}, 'brand' => function ($q){
			                		$q->select("id", "name");
								}, 'category' => function ($q){
			                		$q->select("id", "name");
								}, 'type' => function ($q){
			                		$q->select("id", "name");
								}, 'strain' => function ($q){
			                		$q->select("id", "name");
								}])->whereHas('dispensary' , function ($q)  use ($lat, $lng) {
			                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
			                     ( 3959 * acos( cos( radians(?) ) *
			                       cos( radians( lat ) )
			                       * cos( radians( lng ) - radians(?)
			                       ) + sin( radians(?) ) *
			                       sin( radians( lat ) ) )
			                     ) AS distance", [$lat, $lng, $lat])->whereNotNull('lat')->whereNotNull('lng');
								})->whereHas('brand' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('category' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('type' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('strain' , function ($q) {
								    $q->where('status', 'active');
								})->select('id', 'product_code', 'brand_id', 'product_sku', 'parent_id', 'sub_parent_id', 'dispensary_id', 'strain_id', 'sub_strain_id', 'type_id', 'amount', 'thc', 'cbd', 'name', 'description', 'price_color_code', 'qty', 'price', 'discount_price', 'manage_stock', 'is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', image) image_url"), 'image_url as image', 'product_url', 'updated_at as created_date')->where('status','active');
			                	$nearest_favs = $nearest_favs_query->paginate(20);
							}else{
								$device_id = $request->device_id;
								$nearest_favs_query = Product::with(['favourite' => function ($q)  use ($device_id) {
										$q->where('device_id', $device_id);
										$q->where('status', 'active');
										$q->select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"));
									}])->whereHas('favourite' , function ($q)  use ($device_id) {
										$q->where('device_id', $device_id);
										//$q->where('user_id', 0);
										$q->where('status', 'active');
										$q->select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"));
									})->with(['dispensary' => function ($q)  use ($lat, $lng) {
			                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
			                     ( 3959 * acos( cos( radians(?) ) *
			                       cos( radians( lat ) )
			                       * cos( radians( lng ) - radians(?)
			                       ) + sin( radians(?) ) *
			                       sin( radians( lat ) ) )
			                     ) AS distance", [$lat, $lng, $lat]);
								}, 'brand' => function ($q){
			                		$q->select("id", "name");
								}, 'category' => function ($q){
			                		$q->select("id", "name");
								}, 'type' => function ($q){
			                		$q->select("id", "name");
								}, 'strain' => function ($q){
			                		$q->select("id", "name");
								}])->whereHas('dispensary' , function ($q)  use ($lat, $lng) {
			                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
			                     ( 3959 * acos( cos( radians(?) ) *
			                       cos( radians( lat ) )
			                       * cos( radians( lng ) - radians(?)
			                       ) + sin( radians(?) ) *
			                       sin( radians( lat ) ) )
			                     ) AS distance", [$lat, $lng, $lat])->whereNotNull('lat')->whereNotNull('lng');
								})->whereHas('brand' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('category' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('type' , function ($q) {
								    $q->where('status', 'active');
								})->whereHas('strain' , function ($q) {
								    $q->where('status', 'active');
								})->select('id', 'product_code', 'brand_id', 'product_sku', 'parent_id', 'sub_parent_id', 'dispensary_id', 'strain_id', 'sub_strain_id', 'type_id', 'amount', 'thc', 'cbd', 'name', 'description', 'price_color_code', 'qty', 'price', 'discount_price', 'manage_stock', 'is_featured', DB::raw("CONCAT('" . URL::asset("uploads/products") . "/', image) image_url"), 'image_url as image', 'product_url', 'updated_at as created_date')->where('status','active');
			                	$nearest_favs = $nearest_favs_query->paginate(20);
							}
							
							
			                if (count($nearest_favs) > 0) {
			                	$this->response = array(
									"status" => 200,
									"message" => ResponseMessages::getStatusCodeMessages(125),
									"data" => !empty($nearest_favs) ? $nearest_favs : null,
								);
			                }else{
			                	$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(520),
									"data" => null,
								);
			                }
						/*}else{
							$this->response = array(
								"status" => 100,
								"message" => ResponseMessages::getStatusCodeMessages(534),
								"data" => null,
							);
						}*/
						
					/*}else{
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(5),
							"data" => null,
							"logout" => 1,
						);
					}*/	
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	public function settingRule(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type", "rule_name"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ($request->rule_name == 'currency' || $request->rule_name == 'sms_sender_id' || $request->rule_name == 'image_quality' || $request->rule_name == 'sender_id' || $request->rule_name == 'png_image_quality' || $request->rule_name == 'referrer_amount' || $request->rule_name == 'refer_share_message' || $request->rule_name == 'ios_url_user' || $request->rule_name == 'android_url_user' || $request->rule_name == 'call_us' || $request->rule_name == 'legal') {
					if ($user = User::find($request->user_id)) {
						if ($request->device_type != 'web') {
							$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
						}
					}
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => BusRuleRef::where("rule_name", $request->rule_name)->first()->rule_value
					);
				}else{
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(530),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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

	// function called to supportStore
	public function supportStore(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("name", "email", "description"));
		try {
			$rules = array(
				'name' => 'required',
				'email' => 'required',
				'description' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'name' => 'Name',
				'email' => 'Email',
				'description' => 'Description',
			];	
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$errors = $validate->errors();
				$this->response = array(
					"status" => 300,
					"message" => $errors->first(),
					"data" => null,
					"errors" => $errors,
				);
			} else {
				$Support = new Support;
				$Support->name = ucfirst($request->name);
				$Support->email = $request->email;
				$Support->description = ucfirst($request->description);
				if ($Support->save()) {
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(218),
						"data" => null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(221),
						"data" => null,
					);
				}
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
	// function called to display match user list sections
	public function planList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				
				/*if (\Request::header('Authorization') != '') {
					if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
						$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);

						$resultes = Plan::select('id', 'title','amount', 'duration_text')->where('status', 'active')->first();
						if ($resultes) {
		                	$this->response = array(
								"status" => 200,
								"message" => ResponseMessages::getStatusCodeMessages(125),
								"data" => !empty($resultes) ? $resultes : null,
							);
		                }else{
		                	$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(507),
								"data" => null,
							);
		                }
					}else{
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(5),
							"data" => null,
						);
					}
				}else{*/
					$resultes = Plan::select('id', 'title','amount', 'duration_text')->where('status', 'active')->first();
					if ($resultes) {
	                	$this->response = array(
							"status" => 200,
							"message" => ResponseMessages::getStatusCodeMessages(125),
							"data" => !empty($resultes) ? $resultes : null,
						);
	                }else{
	                	$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(507),
							"data" => null,
						);
	                }
				//}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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

// function called to display category List sections
	public function categoryListOld(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				$query = Category::select('id', 'name', 'is_defalt')->where('status', 'active');
				if (isset($request->category_id)) {
					$query->where('parent_id', $request->category_id);
				} else {
					$query->whereNULL('parent_id');
				}
				$category = $query->orderBy('order_no', 'asc')->paginate(2000);
				if ($category->count() > 0) {
					foreach ($category as &$value) {
						$value->subcategory_count = count($value->childCat);
						unset($value->childCat);
					}
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($category) ? $category : null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(507),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	
// function called to display category List sections
	public function categoryList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				$query = Category::select('id', 'name', 'is_defalt')->where('status', 'active');
				if (isset($request->category_id)) {
					if (isset($request->radius) && !empty($request->radius)) {
						$radius = $request->radius;
					}else{
						$radius = 25;
					}
					if (isset($request->lat) && !empty($request->lat)) {
						$lat = $request->lat;
					}else{
						$lat = '-7.0157404';
					}
					if (isset($request->lng) && !empty($request->lng)) {
						$lng = $request->lng;
					}else{
						$lng = '110.4171283';
					}
					if (isset($request->type) && !empty($request->type)) {
						$returntype = $request->type;
					}else{
						$returntype = 'home';
					}
					$query->where('parent_id', $request->category_id)->whereHas('childCatProduct' , function ($q1) use ($lat, $lng, $radius, $returntype) {
						    $q1->where('status', 'active');
						    $q1->whereHas('dispensary' , function ($q)  use ($lat, $lng, $radius, $returntype) {
		                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
		                     ( 3959 * acos( cos( radians(?) ) *
		                       cos( radians( lat ) )
		                       * cos( radians( lng ) - radians(?)
		                       ) + sin( radians(?) ) *
		                       sin( radians( lat ) ) )
		                     ) AS distance", [$lat, $lng, $lat])->having("distance", "<", $radius);
		                		if ($returntype == 'home') {
		                			$q->where('manage_stock', 1);
		                		}
		                		$q->whereNotNull('lat')->whereNotNull('lng');
							})->whereHas('brand' , function ($q) {
							    $q->where('status', 'active');
							});
						});
				} else {
					$query->whereNULL('parent_id');
				}
				$category = $query->orderBy('order_no', 'asc')->paginate(2000);
				if ($category->count() > 0) {
					foreach ($category as &$value) {
						$value->subcategory_count = count($value->childCat);
						unset($value->childCat);
					}
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($category) ? $category : null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(507),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
		  //  dd($ex);
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}
	// function called to display doctor List sections
	public function doctorList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("name", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$query = Doctor::selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, zipcode, email, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/doctors") . "/', image) image")->where('status', 'active');
				if (!empty($name)) {
                    $query->where('name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
                }
				$doctor = $query->orderBy('created_at', 'desc')->paginate(20);
				if ($doctor->count() > 0) {
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($doctor) ? $doctor : null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(507),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
	// function called to display amount List sections
	public function amountListNew(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("category_id", "device_id", "device_token", "device_type"));
		try {
			$rules = array(
				'category_id' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'category_id' => 'Category',
			];	
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$errors = $validate->errors();
				$this->response = array(
					"status" => 300,
					"message" => $errors->first(),
					"data" => null,
					"errors" => $errors,
				);
			} else {
				if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
					if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
						$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
					}
					$query = Product::select("amount")->groupBy('amount')->whereNotNull('amount')->where('amount','!=','')->where('amount', '!=', 'N/A')->where('status', 'active');
					if (isset($request->category_id) && !empty($request->category_id)) {
						$query->where('parent_id', $request->category_id);
					}
					if (isset($request->type_id) && !empty($request->type_id)) {
						$query->where('type_id', $request->type_id);
					}
					if (isset($request->strain_id) && !empty($request->strain_id)) {
						$query->where('strain_id', $request->strain_id);
					}
					$doctor = $query->orderByRaw('CONVERT(amount, SIGNED) asc')->paginate(2000);
					if ($doctor->count() > 0) { 
						$this->response = array(
							"status" => 200,
							"message" => ResponseMessages::getStatusCodeMessages(125),
							"data" => !empty($doctor) ? $doctor : null,
						);
					} else {
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(507),
							"data" => null,
						);
					}
				}else{
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(515),
						"data" => null,
					);
				}
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

	// function called to display home page sections
	public function amountList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("radius", "category_id", "name","lat", "lng", "type_id", "strain_id", "sort_by", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
			    
		
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				$amount = '';
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
                $nearest_dispensaries_query = Product::with(['dispensary' => function ($q)  use ($lat, $lng, $radius, $sort_by) {
                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
					}, 'brand' => function ($q){
                		$q->select("id", "name");
					}, 'category' => function ($q){
                		$q->select("id", "name");
					}, 'type' => function ($q){
                		$q->select("id", "name");
					}, 'strain' => function ($q){
                		$q->select("id", "name");
					}])->whereHas('brand' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('category' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('type' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('strain' , function ($q) {
		                $q->where('status', 'active');
					})->selectRaw("products.id, products.brand_id, products.parent_id, products.dispensary_id, products.strain_id, products.type_id, products.amount, ( 3959 * acos( cos( radians(?) ) *
	                       cos( radians( lat ) )
	                       * cos( radians( lng ) - radians(?)
	                       ) + sin( radians(?) ) *
	                       sin( radians( lat ) ) )
	                     ) AS distance", [$lat, $lng, $lat])->where('products.status','active');
					$nearest_dispensaries_query->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'products.brand_id', '=', 'companies.id')
						->where('dispensaries.status', 'active')
						->where('companies.status', 'active')
						->having("distance", "<", $radius)
						->whereNotNull('lat')
						->whereNotNull('lng');
                	if ($category_id != 0) {
	                    $nearest_dispensaries_query->where('products.parent_id', $category_id);
	                }
	                if ($type_id != 0) {
	                    $nearest_dispensaries_query->where('products.type_id', $type_id);
	                }
	                if ($strain_id != 0) {
	                    $nearest_dispensaries_query->where('products.strain_id', $strain_id);
	                }
                    if ($amount != '') {
						$nearest_dispensaries_query->where("products.amount", $amount);
                    }
                    if (isset($request->dispensary_id) && !empty($request->dispensary_id)) {
                        $nearest_dispensaries_query->where('products.dispensary_id', $request->dispensary_id);
                    }
                    if ($name != '') {
	                    $nearest_dispensaries_query->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')
			                   ->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
	                    //$nearest_dispensaries_query->where(DB::raw('LOWER(products.name)'), 'LIKE', '%'.strtolower($name).'%')->orWhere(DB::raw('LOWER(companies.name)'), 'LIKE', '%'.strtolower($name).'%');
	                }
                	$nearest_dispensaries = $nearest_dispensaries_query->whereNotNull('products.amount')->where('products.amount', '!=', 'N/A')->orderByRaw('CONVERT(products.amount, SIGNED) asc')/*->groupByRaw('products.amount')*/->where('products.manage_stock', 1)->paginate(2000);

                if (count($nearest_dispensaries) > 0) {
                    
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{

                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			//echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}
	
	// function called to display home page sections
	public function amountListDetail(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("radius", "category_id", "name","lat", "lng", "type_id", "strain_id", "sort_by", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
			    
		
				if (isset(Auth::user()->id)) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->name) && !empty($request->name)) {
					$name = $request->name;
				}else{
					$name = '';
				}
				$sort_by = 'price';
				if (isset($request->sort_by) && !empty($request->sort_by)) {
					if ($request->sort_by == 'price') {
						$sort_by = 'price';
					}else if($request->sort_by == 'thc'){
						$sort_by = 'thc';
					}else{
						$sort_by = 'distance';
					}
				}
				$amount = '';
				if (isset($request->amount) && !empty($request->amount)) {
					$amount = $request->amount;
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 25;
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->strain_id) && !empty($request->strain_id)) {
					$strain_id = $request->strain_id;
				}else{
					$strain_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    if($strain_id == 0 && $type_id == 0 && $name == ''){
					        $category_id = $catCheck->id;
					    }else{
					        $category_id = 0;
					    }
						
					}else{
						$category_id = 0;
					}
				}
                $nearest_dispensaries_query = Product::with(['dispensary' => function ($q)  use ($lat, $lng, $radius, $sort_by) {
                		$q->selectRaw("id, name, phone_code, phone_number, address, lat, lng, city, state, country, description, DATE_FORMAT(created_at,'%b %d') as created_date, CONCAT('" . URL::asset("uploads/brands") . "/', image) image,
                     ( 3959 * acos( cos( radians(?) ) *
                       cos( radians( lat ) )
                       * cos( radians( lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( lat ) ) )
                     ) AS distance", [$lat, $lng, $lat]);
					}, 'brand' => function ($q){
                		$q->select("id", "name");
					}, 'category' => function ($q){
                		$q->select("id", "name");
					}, 'type' => function ($q){
                		$q->select("id", "name");
					}, 'strain' => function ($q){
                		$q->select("id", "name");
					}])->whereHas('brand' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('category' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('type' , function ($q) {
		                $q->where('status', 'active');
					})->whereHas('strain' , function ($q) {
		                $q->where('status', 'active');
					})->selectRaw("products.id, products.brand_id, products.parent_id, products.dispensary_id, products.strain_id, products.type_id, products.amount, ( 3959 * acos( cos( radians(?) ) *
	                       cos( radians( lat ) )
	                       * cos( radians( lng ) - radians(?)
	                       ) + sin( radians(?) ) *
	                       sin( radians( lat ) ) )
	                     ) AS distance", [$lat, $lng, $lat])->where('products.status','active');
					$nearest_dispensaries_query->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'products.brand_id', '=', 'companies.id')
						->where('dispensaries.status', 'active')
						->where('companies.status', 'active')
						->having("distance", "<", $radius)
						->whereNotNull('lat')
						->whereNotNull('lng');
                	if ($category_id != 0) {
	                    $nearest_dispensaries_query->where('products.parent_id', $category_id);
	                }
	                if ($type_id != 0) {
	                    $nearest_dispensaries_query->where('products.type_id', $type_id);
	                }
	                if ($strain_id != 0) {
	                    $nearest_dispensaries_query->where('products.strain_id', $strain_id);
	                }
                    if ($amount != '') {
						$nearest_dispensaries_query->where("products.amount", $amount);
                    }
                    if (isset($request->dispensary_id) && !empty($request->dispensary_id)) {
                        $nearest_dispensaries_query->where('products.dispensary_id', $request->dispensary_id);
                    }
                    if ($name != '') {
	                    $nearest_dispensaries_query->where(function($query) use ($name){
			                    $query->where('products.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			                    $query->orWhere('products.description', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]')
			                   ->orWhere('companies.name', 'REGEXP', '[[:<:]]'.$name.'[[:>:]]');
			            });
	                    //$nearest_dispensaries_query->where(DB::raw('LOWER(products.name)'), 'LIKE', '%'.strtolower($name).'%')->orWhere(DB::raw('LOWER(companies.name)'), 'LIKE', '%'.strtolower($name).'%');
	                }
                	$nearest_dispensaries = $nearest_dispensaries_query->whereNotNull('products.amount')->where('products.amount', '!=', 'N/A')->orderByRaw('CONVERT(products.amount, SIGNED) asc')/*->groupByRaw('products.amount')*/->paginate(2000);

                if (count($nearest_dispensaries) > 0) {
                    
                	$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($nearest_dispensaries) ? $nearest_dispensaries : null,
					);
                }else{

                	$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(520),
						"data" => null,
					);
                }
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
				);
			}
		} catch (\Exception $ex) {
			//echo '<pre>'; print_r($ex); die;
			$this->response = array(
				"status" => 501,
				"message" => ResponseMessages::getStatusCodeMessages(501),
				"data" => null,
			);
		}
		$this->shut_down($request);
		exit;
	}
// function called to display type List sections
	public function productTypeList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				$query = ProductType::select('id', 'name')->where('status', 'active');
				$type = $query->orderBy('created_at', 'desc')->paginate(20);
				if ($type->count() > 0) {
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($type) ? $type : null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(507),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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
// function called to display strain List sections
	public function strainList(Request $request) {
		// check keys are exist
		$this->checkKeys(array_keys($request->all()), array("radius","lat", "lng", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
				}
				if (isset($request->radius) && !empty($request->radius)) {
					$radius = $request->radius;
				}else{
					$radius = 5000;
				}
				if (isset($request->lat) && !empty($request->lat)) {
					$lat = $request->lat;
				}else{
					$lat = '-7.0157404';
				}
				if (isset($request->lng) && !empty($request->lng)) {
					$lng = $request->lng;
				}else{
					$lng = '110.4171283';
				}
				
				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$type_id = $request->type_id;
				}else{
					$type_id = 0;
				}
				if (isset($request->category_id) && !empty($request->category_id)) {
					$category_id = $request->category_id;
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
					    
					        $category_id = $catCheck->id;
					   
						
					}else{
						$category_id = 0;
					}
				}
				/*$query = Strain::whereHas('brand' , function ($q) {
		                $q->where('status', 'active');
					})->selectRaw("strains.id, strains.name, ( 3959 * acos( cos( radians(?) ) *
	                       cos( radians( lat ) )
	                       * cos( radians( lng ) - radians(?)
	                       ) + sin( radians(?) ) *
	                       sin( radians( lat ) ) )
	                     ) AS distance", [$lat, $lng, $lat])->where('strains.status','active');
					$query->leftJoin('dispensaries', 'strains.dispensary_id', '=', 'dispensaries.id')
						->having("distance", "<", $radius)
						->where('dispensaries.status', 'active')
						->whereNotNull('lat')
						->whereNotNull('lng');
				$strain = $query->orderBy('strains.name', 'asc')->paginate(2000);*/

			/*	$query = Strain::select('strains.id', 'strains.name')
								->join('products', 'products.strain_id', '=', 'strains.id')
								->where('strains.status', 'active')
								->where('strains.name', '!=', 'N/A')
								->groupBy('products.strain_id');
				if (isset($request->category_id) && !empty($request->category_id)) {
					$query->where('products.parent_id', $request->category_id);
				}else{
					if ($catCheck = Category::select('id')->where('status', 'active')->where('is_defalt', 'true')->where('type', 'category')->first()) {
						$query->where('products.parent_id', $catCheck->id);
					}
				}

				
				if (isset($request->type_id) && !empty($request->type_id)) {
					$query->where('products.type_id', $request->type_id);
				}
				$strain = $query->orderBy('strains.order_no', 'asc')->paginate(2000);*/
				
				
				$query = Strain::selectRaw("strains.id, strains.name, products.brand_id, products.parent_id, products.dispensary_id, products.strain_id, products.type_id, products.amount, ( 3959 * acos( cos( radians(?) ) *
	                       cos( radians( lat ) )
	                       * cos( radians( lng ) - radians(?)
	                       ) + sin( radians(?) ) *
	                       sin( radians( lat ) ) )
	                     ) AS distance", [$lat, $lng, $lat])->where('products.status','active');
					$query->join('products', 'products.strain_id', '=', 'strains.id')->join('dispensaries', 'products.dispensary_id', '=', 'dispensaries.id')->join('companies', 'products.brand_id', '=', 'companies.id')
						->where('dispensaries.status', 'active')
						->where('companies.status', 'active')
						->where('strains.status', 'active')
						->where('strains.name', '!=', 'N/A')
						->groupBy('products.strain_id')
						->having("distance", "<", $radius)
						->whereNotNull('lat')
						->whereNotNull('lng');
                	if ($category_id != 0) {
	                    $query->where('products.parent_id', $category_id);
	                }
	                if ($type_id != 0) {
	                    $query->where('products.type_id', $type_id);
	                }
	           $strain = $query->orderBy('strains.order_no', 'asc')->paginate(2000);
				
				
				
				
				if ($strain->count() > 0) {
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($strain) ? $strain : null,
					);
				} else {
					$this->response = array(
						"status" => 300,
						"message" => ResponseMessages::getStatusCodeMessages(507),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(515),
					"data" => null,
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

	private function getGoogleAccessToken(){
		require "./vendor/autoload.php";
	    $credentialsFilePath = public_path('download/test-project-bcfcb-firebase-adminsdk-1zlxv-4a68f6dcfe.json'); 
	    $client = new \Google_Client();
	    $client->setAuthConfig($credentialsFilePath);
	    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
	    $client->refreshTokenWithAssertion();
	    $token = $client->getAccessToken();
	    return $token['access_token'];
	}

	// function called testApi
	public function testApi(Request $request) {
		try{
			$mobiles = urlencode('+1-913-871-6446');
			$verify_code = 1234;
			$otp_message = "Laravel's one time passcode (OTP) is ".$verify_code.".";

			$token = env('TOKAN_FIRST');
			//$sms = urlencode($otp_message);
			$sms = $otp_message;

			$sendSms = ['message' => $sms];
			
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
			  echo "cURL Error #:" . $err;
			} else {
			  echo $response;
			}
	    	$this->response = array(
				"status" => 200,
				"message" => 'Test Sms Done',
				"data" => null,
			);
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

	public function testNotificationSend(Request $request) { 
		$this->checkKeys(array_keys($request->all()), array("user_id", 'token',  "extramessage",  "title", "type"));
		try { 

			$rules = array(
				'user_id' => 'required',
				'token' => 'required',
				'title' => 'required',
				'extramessage' => 'required',
				'type' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'user_id' => 'User Name',
				'token' => 'token',
				'title' => 'title',
				'extramessage' => 'extramessage',
				'type' => 'type',
			];	
			$validate->setAttributeNames($attr);
			if ($validate->fails()) {
				$errors = $validate->errors();
				$this->response = array(
					"status" => 300,
					"message" => $errors->first(),
					"data" => null,
					"errors" => $errors,
				);
			} else {   
				$fcmUrl = 'https://fcm.googleapis.com/v1/projects/test-project-bcfcb/messages:send';
				$token = $request->token;
				$title = $request->title;
				$extramessage = $request->extramessage; 
				$type = $request->type; 
				$sound = true; 
				$alert = true;
		        
		        
		        $notification = [
		            'title' => $title,
		            'body' => $extramessage,
		        ];
		        
		        $android = [
		        	'notification' => [
		        		'click_action' => 'android.intent.action.MAIN'
		        	]
		        ];
		        $apns = [
		        	'headers' => [
		        		'apns-priority' => '10'
		        	],
		        	'payload' => [
		        		'aps' => [
		        			'badge' => 1
		        		],
			        	'mutable_content' => 1,
			        	'content_available' => 1,
			        	'data' => [
			        		'title' => '',
			        		'body' => '',
			        		'image' => ''
			        	]
		        	]
		        ];
		        $messageBody = [
		        	'token'=> $token,  
		            'notification' => $notification,
		            'android' => $android,
		            'apns' => $apns,
		        ];
		        $fcmNotification = [
		            'message'        => $messageBody
		        ];
		        
		        $headers = array();
		        $headers[] = 'Content-Type: application/json';
		        $headers[] = 'Authorization: Bearer '. $this->getGoogleAccessToken();
		        $data = json_encode($fcmNotification);

		        $ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $fcmUrl);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));

				$result = curl_exec($ch);
				echo '<pre>'; print_r($result); die;
				if ($result === FALSE) {
				    die('Curl failed: ' . curl_error($ch));
				}

				curl_close($ch);

		        $this->response = array(
					"status" => 200,
					"message" => 'Send Notification',
					"data" => null,
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

}


	
