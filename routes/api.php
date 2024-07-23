<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*Route::get('unauthorized', function () {
    return response()->json(['status' => 403, 'message' => 'Unauthorizeddsgfdsfg.', 'data' => null]);
})->name('api.unauthorized');*/
Route::get("login", "Api\ApiController@appLogin")->name('login');
Route::post("login", "Api\ApiController@login");
Route::post("signUp", "Api\ApiController@signUp");
Route::post("resendOTP", "Api\ApiController@resendOTP");
Route::post("verifyUser", "Api\ApiController@verifyUser");
Route::post("socialLogin", "Api\ApiController@socialLogin");
Route::post("socialCheck", "Api\ApiController@socialCheck");
Route::post("dispensaryList", "Api\ApiController@dispensaryList");
Route::post("brandList", "Api\ApiController@brandList");
Route::post("stateList", "Api\ApiController@stateList");
Route::post("allowLocation", "Api\ApiController@allowLocation");
Route::post("dispensaryWiseProductList", "Api\ApiController@dispensaryWiseProductList");
Route::post("dispensaryWiseProductListHome", "Api\ApiController@dispensaryWiseProductListHome");
Route::post("homePageData", "Api\ApiController@homePageData");
Route::post("mapHomePageData", "Api\ApiController@mapHomePageData");
Route::post("productDetails", "Api\ApiController@productDetails");

Route::post("supportStore", "Api\ApiController@supportStore");

Route::post("settingRule", "Api\ApiController@settingRule");
Route::post("categoryList", "Api\ApiController@categoryList");
Route::post("productTypeList", "Api\ApiController@productTypeList");
Route::post("strainList", "Api\ApiController@strainList");

Route::post("doctorList", "Api\ApiController@doctorList");
Route::post("amountList", "Api\ApiController@amountList");
Route::post("amountListDetail", "Api\ApiController@amountListDetail");
Route::post("testApi", "Api\ApiController@testApi");
Route::post("feedbackStore", "Api\ApiController@feedbackStore");
Route::middleware('auth:api')->group(function () {
    Route::post("planList", "Api\AuthApiController@planList");
    Route::post("logout", "Api\AuthApiController@logout");
    Route::post("updateUser", "Api\AuthApiController@updateUser");
    Route::post("profileDetails", "Api\AuthApiController@profileDetails");
    Route::post("manageUserFavourite", "Api\AuthApiController@manageUserFavourite");
    Route::post("getUserFavourites", "Api\AuthApiController@getUserFavourites");
    Route::post("upgradePlan", "Api\AuthApiController@upgradePlan");
    Route::post("stopNotification", "Api\AuthApiController@stopNotification");
    Route::post("userPlanList", "Api\AuthApiController@userPlanList");
    Route::post("statusUpdate", "Api\AuthApiController@statusUpdate");
    Route::post("stripSubscriptionCancel", "Api\AuthApiController@stripSubscriptionCancel");
});
Route::post("manageUserFavourite", "Api\ApiController@manageUserFavourite");
Route::post("getUserFavourites", "Api\ApiController@getUserFavourites");
Route::post("planList", "Api\ApiController@planList");
Route::any('autoDebit', ['as'=>'autoDebit','uses'=>'SubscriptionController@autoDebit'])->name('autoDebit'); 
