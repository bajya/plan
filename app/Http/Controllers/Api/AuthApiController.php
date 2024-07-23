<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Library\Helper;
use App\Library\Notify;
use App\Library\ResponseMessages;
use App\User;
use App\UserOTP;
use App\UserDevice;
use App\ProductFavourite;
use App\UserPlan;
use App\Dispensary;
use App\Feedback;
use App\Transaction;
use App\Product;
use App\BusRuleRef;
use App\Plan;
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
use Stripe;
use Exception;

class AuthApiController extends Controller {
	
	public function __construct()
    {
        $this->middleware('auth');
    }
	// function called to updateUser
	public function updateUser(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("name", "device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				$rules = array(
					'name' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'name' => 'Name',
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
						if ((isset($request->name) && !empty($request->name))) {
							$user->name = ucfirst($request->name);
						}
						if ($request->hasFile('image')) {
							$file_name = '';
							$file = $request->file('image');
							if($file){
								$destinationPath = public_path().'/img/avatars/';
								$originalFile = $file->getClientOriginalName();
								$newImage = rand(111,999).time().$originalFile;
								$file->move($destinationPath, $newImage);
								$file_name = $newImage;
								$user->avatar = $file_name;
								$user->save();
								/*$url = 'img/avatars/' . $newImage;
								$thumb_img = Image::make($url)->resize(200, 200);
								$thumb_img->save('img/avatars/thumb/'.$newImage,80);
								$file_name =  $thumb_img->basename;*/
							}
						} 
						if ($user->save()) {  
							$user = User::select('id', DB::raw("CONCAT('" . URL::asset("img/avatars") . "/', avatar) image"), 'name', 'phone_code', 'mobile', 'email', 'notification', 'email_alert', 'subscription_id', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('id', $user->id)->first();
							if (!empty($user)) { 
		                		$resultes = UserPlan::select('id', 'title', 'amount', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"), 'duration_month', 'duration_text')->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('user_id', $user->id)->where('status', 'active')->first();
								if ($resultes) {
				                	$user->plan = $resultes;
				                }else{
				                	$user->plan = array('id' => 0, 'title' => '', 'amount' => '0', 'plan_expire_time' => '', 'duration_month' => 0, 'duration_text' => '');
				                }
								
								$this->response = array(
									"status" => 200,
									"message" => ResponseMessages::getStatusCodeMessages(124),
									"data" => !empty($user) ? $user : null,
								);
							}else{
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(5),
									"data" => null,
								);
							}
						} else {
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(102),
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


	public function logout(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
					//ProductFavourite::where('user_id', $user->id)->update(array('device_id' => ''));
					
					$updateData = array('user_id' => $user->id, 'device_id' => 'NULL');
					ProductFavourite::where('device_id', $request->device_id)->where('user_id', $user->id)->update($updateData);
					$this->logoutUserDevice($user->id, $request->device_id, $request->device_token);
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(10),
						"data" => null,
					);
				} else {
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(5),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(214),
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
	public function profileDetails(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')) {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->where('status', 'active')->first())) {
					$users = $this->commanUserDetails($user->id, $user->id);
					$this->response = array(
						"status" => 200,
						"message" => ResponseMessages::getStatusCodeMessages(125),
						"data" => !empty($users) ? $users : null,
					);
				} else {
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(5),
						"data" => null,
					);
				}
			}else{
				$this->response = array(
					"status" => 300,
					"message" => ResponseMessages::getStatusCodeMessages(214),
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
										//$fav->device_id = $request->device_id;
										$fav->device_id = '';
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
										$q->where('status', 'active');
											$q->groupBy('product_id')/*->whereHas('user' , function ($q_fav) {
        								    $q_fav->where('status', 'active');
        								})*/;
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
									//	$q->where('user_id', 0);
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
	public function upgradePlan(Request $request) { 
		$this->checkKeys(array_keys($request->all()), array('plan_id', "payment_method",  "txn_id", "device_id", "device_token", "device_type"));
		try {
				$rules = array(
					'plan_id' => 'required',
					'payment_method' => 'required',
					'txn_id' => 'required',
				);
				$validate = Validator($request->all(), $rules);
				$attr = [
					'plan_id' => 'Plan Name',
					'payment_method' => 'Payment Method',
					'txn_id' => 'Transaction Id',
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
						if ($plan = Plan::where("id", $request->plan_id)->first()) {
							$days = $plan->duration_month;
							$user->plan_id = $request->plan_id;
							$user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));  
							if ($user->save()) {
								$array_old = array('status' => 'old');
								UserPlan::where('user_id', $user->id)->update($array_old);
								$user_plan = new UserPlan();
			                  	$user_plan->user_id = $user->id;
			                  	$user_plan->plan_id = $request->plan_id;
			                  	$user_plan->title = $plan->title;
			                  	$user_plan->amount = $plan->amount;
			                  	$user_plan->duration_text = $plan->duration_text;
			                  	$user_plan->duration_month = $plan->duration_month;
			                  	$user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$days.' days'));
			                  	if ($user_plan->save()) {
			                  		$transaction=new Transaction();
				                  	$transaction->user_id = $user->id;
				                  	$transaction->status = 'active';
				                  	$transaction->transaction_type = 'plan';
				                  	$transaction->item_id = $plan->id;
				                  	$transaction->txn_id = $request->txn_id;
				                  	$transaction->payment_method = $request->payment_method;
				                  	$transaction->before_wallet_amount = '0.00';
				                  	$transaction->after_wallet_amount = '0.00';
				                  	$transaction->amount = $plan->amount;
				                  	$transaction->title = 'Plan Upgrade';
				                  	$transaction->message = 'Plan Upgrade +'.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$plan->amount;
				                  	$transaction->save();
			                  	}
			                  	$user = User::select('id', DB::raw("CONCAT('" . URL::asset("img/avatars") . "/', avatar) image"), 'name', 'phone_code', 'mobile', 'email', 'notification', 'email_alert', 'subscription_id', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"))->where('id', $user->id)->first();
								if (!empty($user)) {
			                		$resultes = UserPlan::select('id', 'title', 'amount', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"), 'duration_month', 'duration_text')->where('user_id', $user->id)->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('status', 'active')->first();
									if ($resultes) {
					                	$user->plan = $resultes;
					                }else{
					                	$user->plan = array('id' => 0, 'title' => '', 'amount' => '0', 'plan_expire_time' => '', 'duration_month' => 0, 'duration_text' => '');
					                }
									$this->response = array(
										"status" => 200,
										"message" => ResponseMessages::getStatusCodeMessages(125),
										"data" => !empty($user) ? $user : null
									);
								}else{
									$this->response = array(
										"status" => 403,
										"message" => ResponseMessages::getStatusCodeMessages(5),
										"data" => null
									);
								}
							}else{
								$this->response = array(
									"status" => 509,
									"message" => ResponseMessages::getStatusCodeMessages(221),
									"data" => null,
								);
							}
						}else{
							$this->response = array(
								"status" => 509,
								"message" => ResponseMessages::getStatusCodeMessages(531),
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
				}
			
		} catch (\Exception $ex) {
			dd($ex);
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
				} else {
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(5),
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
	public function stopNotification(Request $request) { 
		$this->checkKeys(array_keys($request->all()), array('status', 'lat', 'lng', 'product_id', 'days',  "device_id", "device_token", "device_type"));
		try {
			$rules = array(
				'days' => 'required|numeric|min:1|max:30',
				'product_id' => 'required|numeric|min:1',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'days' => 'Days',
				'product_id' => 'Product',
			];	
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$errors = $validate->errors();
				if ($errors->first() == 'The Days may not be greater than 30.') {
					$this->response = array(
						"status" => 300,
						"message" => 'Select a value less than 30.',
						"data" => null,
						"errors" => $errors,
					);
				}else{
					$this->response = array(
						"status" => 300,
						"message" => $errors->first(),
						"data" => null,
						"errors" => $errors,
					);
				}
				
			} else {
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
				    
				
					$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
					if ($request->status == 'pause') {
						$user->pause_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$request->days.' days'));
					}else{
						$user->pause_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' - 1 days'));
					}
					  
					if ($user->save()) {
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
						$updateData = array('user_id' => $user->id, 'device_id' => 'NULL');
						ProductFavourite::where('device_id', $request->device_id)->where('user_id', 0)->update($updateData);

						
						if ($getFavData = ProductFavourite::where('user_id', $user->id)->where('product_id', $request->product_id)->first()) {
							if ($request->status == 'pause') {
								/*if ($getFavData->pause_status == 'inactive') {
									$getFavData->pause_status = 'active';
									$getFavData->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
								}else{*/
									//$getFavData->pause_status = 'inactive';
									$getFavData->pause_status = 'active';
									$getFavData->pause_expire_time = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$request->days.' days')); 
								/*}*/
								$getFavData->is_user_status = $request->status;
								
								$updateData2 = array('is_user_status' => $request->status, 'pause_status' => 'active', 'pause_expire_time' => date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$request->days.' days')));
						        ProductFavourite::where('user_id', $user->id)->where('product_id', $request->product_id)->update($updateData2);
							}else{
								$getFavData->pause_status = 'inactive';
								$getFavData->pause_expire_time = date('Y-m-d', strtotime( date('Y-m-d') . " -1 days"));
								$getFavData->is_user_status = $request->status;
								
								$updateData2 = array('is_user_status' => $request->status, 'pause_status' => 'inactive', 'pause_expire_time' => date('Y-m-d', strtotime( date('Y-m-d') . " -1 days")));
						        ProductFavourite::where('user_id', $user->id)->where('product_id', $request->product_id)->update($updateData2);
							}
							
							$getFavData->save();
						}
						$nearest_favs_query = Product::with(['favourite' => function ($q)  use ($user) {
								$q->where('user_id', $user->id);
								$q->where('status', 'active');
								$q->select('id as fav_id', 'product_id', 'is_user_status', 'pause_status', 'pause_expire_time', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"));
							}])->whereHas('favourite' , function ($q)  use ($user) {
								$q->where('user_id', $user->id);
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
					}else{
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(221),
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
	// function called to userPlanList
	public function userPlanList(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
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
					if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())) {
						$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
						$resultes = UserPlan::select('id', 'title', 'amount', 'status', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"))->where('user_id', $user->id)->orderBy('id', 'desc')->paginate(20);
					    if (count($resultes) > 0) {
					    	$this->response = array(
								"status" => 200,
								"message" => '',
								"data" => !empty($resultes) ? $resultes : null,
							);
					    }else{
					    	$this->response = array(
								"status" => 300,
								"message" => '',
								"data" => null,
							);
					    }
					}else{
						$this->response = array(
							"status" => 403,
							"message" => ResponseMessages::getStatusCodeMessages(5),
							"data" => null,
							"logout" => 1,
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
	
	public function statusUpdate(Request $request) {
		$this->checkKeys(array_keys($request->all()), array('item_id', "item_type", "status", "device_id", "device_token", "device_type"));
		try {
			$rules = array(
				'item_id' => 'required',
				'item_type' => 'required',
				'status' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'item_id' => 'Item',
				'item_type' => 'Item Type ProductFavourite/UserDelete',
				'status' => 'Status Type active/inactive/delete',
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
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())){
					if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')){
						if ($request->device_type != 'web') {
							$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
						}
						if ($request->item_type == 'ProductFavourite' || $request->item_type == 'UserDelete'){
							if ($request->item_type == 'ProductFavourite') {
							 	$model = '\\App\\ProductFavourite';
							}else if ($request->item_type == 'UserDelete') {
								$model = '\\App\\User';
							}else{
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(513),
									"data" => null,
								);
							} 
							if ($model_data = $model::where("id", $request->item_id)->first()) {  
								if ($request->status == 'active' || $request->status == 'inactive') {
									$model_data->is_user_status = $request->status;
									if ($model_data->save()) {
										$this->response = array(
											"status" => 200,
											"message" => ResponseMessages::getStatusCodeMessages(523),
											"data" => null,
										);
									}else{
										$this->response = array(
											"status" => 300,
											"message" => ResponseMessages::getStatusCodeMessages(524),
											"data" => null,
										);
									}
								}else if($request->status == 'delete'){
									if ($insert = User::where("id", $request->item_id)->first()) {
										$insert->status = $request->status;
										if ($insert->save()) {
											if ($insert->subscription_id != '') {
					                            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
					                            $stripe->subscriptions->cancel(
					                              $insert->subscription_id,
					                              []
					                            );
					                            $insert->subscription_id = '';
												$insert->save();
					                        }
											ProductFavourite::where('user_id', $insert->id)->delete();
											ProductFavourite::where('device_id', $request->device_id)->delete();
											$this->logoutUserDevice($insert->id, $request->device_id, $request->device_token);
											$this->response = array(
												"status" => 200,
												"message" => ResponseMessages::getStatusCodeMessages(514),
												"data" => null,
											);
										}else{
											$this->response = array(
												"status" => 300,
												"message" => ResponseMessages::getStatusCodeMessages(502),
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
								}else{
									$this->response = array(
										"status" => 300,
										"message" => ResponseMessages::getStatusCodeMessages(508),
										"data" => null,
									);
								}
							}else{
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(504),
									"data" => null,
								);
							}
						}else{
							$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(513),
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
				}else{
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(5),
						"data" => null,
						"logout" => 1,
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
	public function stripSubscriptionCancel(Request $request) {
		$this->checkKeys(array_keys($request->all()), array("device_id", "device_token", "device_type"));
		try {
			$rules = array(
				'device_id' => 'required',
			);
			$validate = Validator($request->all(), $rules);
			$attr = [
				'device_id' => 'Device Id',
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
				if ((isset(Auth::user()->id)) && ($user = User::where("id", Auth::user()->id)->first())){
					if (($request->device_type == 'android') || ($request->device_type == 'ios') || ($request->device_type == 'web')){
						if ($request->device_type != 'web') {
							$this->updateUserDevice($user->id, $request->device_id, $request->device_token, $request->device_type);
						}
						$data = [];
						$data['name'] = $user->name;
						$data['email'] = $user->email;
						$data['phone_code'] = $user->phone_code;
						$data['mobile'] = $user->mobile;
						Notify::sendMail("emails.user_registration", $data, "Laravel - Subscription Cancel");

						$this->response = array(
							"status" => 200,
							"message" => ResponseMessages::getStatusCodeMessages(202),
							"data" => null,
						);
						/*if ($user->subscription_id != '') {
                            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                            $stripe->subscriptions->cancel(
                              $user->subscription_id,
                              []
                            );
                            $user->subscription_id = '';
							if ($user->save()) {
								$this->response = array(
									"status" => 200,
									"message" => ResponseMessages::getStatusCodeMessages(202),
									"data" => null,
								);
							}else{
								$this->response = array(
									"status" => 300,
									"message" => ResponseMessages::getStatusCodeMessages(502),
									"data" => null,
								);
							}
                        }else{
                        	$this->response = array(
								"status" => 300,
								"message" => ResponseMessages::getStatusCodeMessages(553),
								"data" => null,
							);
                        }*/
						
									
					}else{
						$this->response = array(
							"status" => 300,
							"message" => ResponseMessages::getStatusCodeMessages(515),
							"data" => null,
						);
					}
				}else{
					$this->response = array(
						"status" => 403,
						"message" => ResponseMessages::getStatusCodeMessages(5),
						"data" => null,
						"logout" => 1,
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
	public function commanUserDetails($user_id, $details_id) {
		try
		{ 
			$user = User::select('id', 'name', 'phone_code', 'mobile', 'email', 'notification', 'email_alert', 'subscription_id', 'subscription_id', DB::raw("DATE_FORMAT(created_at,'%b %d') as created_date"),  DB::raw("CONCAT('" . URL::asset("img/avatars") . "/', avatar) image"))->where('id', $details_id)->first();
			if (!empty($user)) {
	 			$resultes = UserPlan::select('id', 'title', 'amount', DB::raw("DATE_FORMAT(plan_expire_time,'%b %d') as plan_expire_time"), 'duration_month', 'duration_text')->where('plan_expire_time', '>=' ,date('Y-m-d'))->where('user_id', $user->id)->where('status', 'active')->first();
				if ($resultes) {
                	$user->plan = $resultes;
                }else{
                	$user->plan = array('id' => 0, 'title' => '', 'amount' => '0', 'plan_expire_time' => '', 'duration_month' => 0, 'duration_text' => '');
                }
				return $user;
			}else{
				return [];
			}
		} catch (\Exception $ex) {
			return [];
		}
		
	}
}