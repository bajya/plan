<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*Route::get('/', function () {
    return view('welcome');
});
Route::get('{slug}', 'HomeController@getMaintenance')->name('getMaintenance');*/
//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode1 = Artisan::call('cache:clear');
   // $exitCode2 = Artisan::call('optimize');
    //$exitCode3 = Artisan::call('route:cache');
    //$exitCode4 = Artisan::call('route:clear');
    $exitCode5 = Artisan::call('view:clear');
    $exitCode6 = Artisan::call('config:cache');
    return '<h1>Cache facade value cleared</h1>';
});
Route::get('scheduler', function() {
    Artisan::call('schedule:run');
});
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/message/{id}', 'HomeController@getMessage')->name('message');
Route::post('message','HomeController@sendMessage');



Route::group(['middleware' => ['auth']], function() {
	Route::group(['prefix'=>'admin','middleware' => ['auth','admin']], function() {
    	// Index and dashboard
		Route::get('/', ['uses' => 'Backend\HomeController@index'])->name('dashboard');
		Route::get('income', ['uses' => 'Backend\HomeController@incomeChart'])->name('Income');
		Route::get('change-password/edit', ['uses' => 'Backend\HomeController@changePassword'])->name('changepassword');
		Route::post('change-password', ['uses' => 'Backend\HomeController@changePassword'])->name('changepasswordPost');

		//admins Module
		Route::get('admins/list', ['uses' => 'Backend\AdminController@index'])->name('admins');
		Route::post('adminsAjax', ['uses' => 'Backend\AdminController@adminsAjax'])->name('adminsAjax');
		Route::get('admins/add', ['uses' => 'Backend\AdminController@create'])->name('createAdmins');
		Route::post('admins/store', ['uses' => 'Backend\AdminController@store'])->name('addAdmins');
		Route::get('admins/edit/{id?}', ['uses' => 'Backend\AdminController@edit'])->name('editAdmins');
		Route::post('admins/update/{id?}', ['uses' => 'Backend\AdminController@update'])->name('updateAdmins');
		Route::get('admins/view/{id?}', ['uses' => 'Backend\AdminController@show'])->name('viewAdmins');
		Route::get('admins/checkAdmins/{id?}', ['uses' => 'Backend\AdminController@checkAdmins'])->name('checkAdmins');
		Route::post('admins/changeStatus', ['uses' => 'Backend\AdminController@updateStatus'])->name('changeStatusAdmins');
		Route::post('admins/changeStatusAjax', ['uses' => 'Backend\AdminController@updateStatusAjax'])->name('changeStatusAjaxAdmins');
		Route::post('/admins/delete', ['uses' => 'Backend\AdminController@destroy'])->name('deleteAdmins');
		Route::post('/admins/bulkdelete', ['uses' => 'Backend\AdminController@bulkdelete'])->name('deleteAdminsBulk');
		Route::post('/admins/bulkupdate_status', ['uses' => 'Backend\AdminController@bulkchangeStatus'])->name('changeStatusAdminsBulk');


		//CMS Module
		Route::get('/cms/list', ['uses' => 'Backend\CMSController@index'])->name('cms');
		Route::get('/cmsAjax', ['uses' => 'Backend\CMSController@cmsAjax'])->name('cmsAjax');
		Route::get('/cms/view/{id?}', ['uses' => 'Backend\CMSController@show'])->name('viewCMS');
		Route::get('/cms/edit/{id?}', ['uses' => 'Backend\CMSController@edit'])->name('editCMS');
		Route::post('/cms/update/{id?}', ['uses' => 'Backend\CMSController@update'])->name('updateCMS');
		Route::post('/cms/deleteFAQ/{id?}', ['uses' => 'Backend\CMSController@destroy'])->name('deleteFAQ');


		//users Module
		Route::get('users/list', ['uses' => 'UserController@index'])->name('users');
		Route::post('usersAjax', ['uses' => 'UserController@usersAjax'])->name('usersAjax');
		Route::get('users/add', ['uses' => 'UserController@create'])->name('createUsers');
		Route::post('users/store', ['uses' => 'UserController@store'])->name('addUsers');
		Route::get('users/edit/{id?}', ['uses' => 'UserController@edit'])->name('editUsers');
		Route::post('users/update/{id?}', ['uses' => 'UserController@update'])->name('updateUsers');
		Route::get('users/view/{id?}', ['uses' => 'UserController@show'])->name('viewUsers');
		Route::get('users/checkUsers/{id?}', ['uses' => 'UserController@checkUsers'])->name('checkUsers');
		Route::post('users/changeStatus', ['uses' => 'UserController@updateStatus'])->name('changeStatusUsers');
		Route::post('users/changeStatusAjax', ['uses' => 'UserController@updateStatusAjax'])->name('changeStatusAjaxUsers');
		Route::post('/users/delete', ['uses' => 'UserController@destroy'])->name('deleteUsers');
		Route::post('/users/bulkdelete', ['uses' => 'UserController@bulkdelete'])->name('deleteUsersBulk');
		Route::post('/users/bulkupdate_status', ['uses' => 'UserController@bulkchangeStatus'])->name('changeStatusUsersBulk');

		Route::get('users/fav/product/list', ['uses' => 'UserController@usersFavProd'])->name('usersFavProd');
		Route::post('usersFavProdAjax', ['uses' => 'UserController@usersFavProdAjax'])->name('usersFavProdAjax');


		//Role Module
		Route::get('/roles/list', ['uses' => 'RoleController@index'])->name('roles');
		Route::post('/roleAjax', ['uses' => 'RoleController@roleAjax'])->name('roleAjax');
		Route::get('/roles/add', ['uses' => 'RoleController@create'])->name('createRole');
		Route::get('/roles/checkRole/{id?}', ['uses' => 'RoleController@checkRole'])->name('checkRole');
		Route::post('/roles/store', ['uses' => 'RoleController@store'])->name('addRole');
		Route::get('/roles/view/{id?}', ['uses' => 'RoleController@show'])->name('viewRole');
		Route::get('/roles/edit/{id?}', ['uses' => 'RoleController@edit'])->name('editRole');
		Route::post('/roles/update/{id?}', ['uses' => 'RoleController@update'])->name('updateRole');
		Route::post('/roles/update_status', ['uses' => 'RoleController@updateStatus'])->name('changeStatusRole');
		Route::post('/roles/update_statusAjax', ['uses' => 'RoleController@updateStatusAjax'])->name('changeStatusAjaxRole');
		Route::post('/roles/delete', ['uses' => 'RoleController@destroy'])->name('deleteRole');
		Route::post('/roles/bulkdelete', ['uses' => 'RoleController@bulkdelete'])->name('deleteRoles');
		Route::post('/roles/bulkupdate_status', ['uses' => 'RoleController@bulkchangeStatus'])->name('changeStatusRoles');


		//Products module
		Route::get('/products/list', ['uses' => 'Backend\ProductController@index'])->name('products');
		Route::post('/productsAjax', ['uses' => 'Backend\ProductController@productsAjax'])->name('productsAjax');
		Route::get('/products/add', ['uses' => 'Backend\ProductController@create'])->name('createProduct');
		Route::post('/products/checkProduct/{id?}', ['uses' => 'Backend\ProductController@checkProduct'])->name('checkProduct');
		Route::post('/products/store', ['uses' => 'Backend\ProductController@store'])->name('addProduct');
		Route::get('/products/view/{id?}', ['uses' => 'Backend\ProductController@show'])->name('viewProduct');
		Route::get('/products/edit/{id?}', ['uses' => 'Backend\ProductController@edit'])->name('editProduct');
		Route::post('/products/update/{id?}', ['uses' => 'Backend\ProductController@update'])->name('updateProduct');
		Route::post('/products/update_status', ['uses' => 'Backend\ProductController@updateStatus'])->name('changeStatusProduct');
		Route::post('/products/update_statusAjax', ['uses' => 'Backend\ProductController@updateStatusAjax'])->name('changeStatusAjaxProduct');
		Route::post('/products/update_stockAjax', ['uses' => 'Backend\ProductController@updateStockAjax'])->name('changeStockAjaxProduct');
		Route::post('/products/delete', ['uses' => 'Backend\ProductController@destroy'])->name('deleteProduct');
		Route::post('/products/bulkdelete', ['uses' => 'Backend\ProductController@bulkdelete'])->name('deleteProducts');
		Route::post('/products/bulkupdate_status', ['uses' => 'Backend\ProductController@bulkchangeStatus'])->name('changeStatusProducts');
		Route::post('/products/bulkupdate_statusAll', ['uses' => 'Backend\ProductController@bulkchangeStatusAll'])->name('changeStatusProductsAll');
		Route::post('/products/sendNotificationProduct', ['uses' => 'Backend\ProductController@sendNotificationProduct'])->name('sendNotificationProduct');
		Route::any('/products/import', ['uses' => 'Backend\ProductController@import'])->name('importProducts');
		Route::get('/products/exportView', ['uses' => 'Backend\ProductController@exportView'])->name('exportView');
		Route::get('/products/export', ['uses' => 'Backend\ProductController@export'])->name('exportProducts');
		Route::post('/products/bulkUpdate', ['uses' => 'Backend\ProductController@bulkUpdate'])->name('bulkProductUpdate');

        Route::post('/products/on_changeAjax', ['uses' => 'Backend\ProductController@on_changeAjax'])->name('on_changeAjax');

        Route::get('/products/imageUpload', ['uses' => 'Backend\ProductController@imageUpload'])->name('imageUpload');
        Route::post('/products/bulkImageUpload', ['uses' => 'Backend\ProductController@bulkImageUpload'])->name('bulkImageUpload');
        Route::get('/productLogsAjax', ['uses' => 'Backend\ProductController@productLogsAjax'])->name('productLogsAjax');

		//Support Module
		Route::get('supports', ['uses' => 'Backend\SupportController@index'])->name('supports');
		Route::get('/supportsAjax', ['uses' => 'Backend\SupportController@supportsAjax'])->name('supportsAjax');
		Route::get('/supports/view/{id?}', ['uses' => 'Backend\SupportController@show'])->name('viewSupport');



		//Category Module
		Route::get('/categories/list', ['uses' => 'Backend\CategoryController@index'])->name('categories');
		Route::post('/categoryAjax', ['uses' => 'Backend\CategoryController@categoryAjax'])->name('categoryAjax');
		Route::get('/categories/add', ['uses' => 'Backend\CategoryController@create'])->name('createCategory');
		Route::post('/categories/checkCategory/{id?}', ['uses' => 'Backend\CategoryController@checkCategory'])->name('checkCategory');
		Route::post('/categories/store', ['uses' => 'Backend\CategoryController@store'])->name('addCategory');
		Route::get('/categories/view/{id?}', ['uses' => 'Backend\CategoryController@show'])->name('viewCategory');
		Route::get('/categories/edit/{id?}', ['uses' => 'Backend\CategoryController@edit'])->name('editCategory');
		Route::post('/categories/update/{id?}', ['uses' => 'Backend\CategoryController@update'])->name('updateCategory');
		Route::post('/categories/update_status', ['uses' => 'Backend\CategoryController@updateStatus'])->name('changeStatusCategory');
		Route::post('/categories/update_statusAjax', ['uses' => 'Backend\CategoryController@updateStatusAjax'])->name('changeStatusAjaxCategory');
		Route::post('/categories/update_statusDefaltAjax', ['uses' => 'Backend\CategoryController@updateStatusDefaltAjax'])->name('changeStatusDefaltAjaxCategory');
		Route::post('/categories/update_statusOrderAjax', ['uses' => 'Backend\CategoryController@updateStatusOrderAjax'])->name('changeStatusOrderAjaxCategory');
		Route::post('/categories/delete', ['uses' => 'Backend\CategoryController@destroy'])->name('deleteCategory');
		Route::post('/categories/bulkdelete', ['uses' => 'Backend\CategoryController@bulkdelete'])->name('deleteCategories');
		Route::post('/categories/bulkupdate_status', ['uses' => 'Backend\CategoryController@bulkchangeStatus'])->name('changeStatusCategories');

		//Feedback Module
		Route::get('feedbacks', ['uses' => 'Backend\FeedbackController@index'])->name('feedbacks');
		Route::get('/feedbacksAjax', ['uses' => 'Backend\FeedbackController@feedbacksAjax'])->name('feedbacksAjax');
		Route::get('/feedbacks/view/{id?}', ['uses' => 'Backend\FeedbackController@show'])->name('viewFeedback');


		//Dispensary Module
		Route::get('/dispensaries/list', ['uses' => 'Backend\DispensaryController@index'])->name('dispensaries');
		Route::post('/dispensaryAjax', ['uses' => 'Backend\DispensaryController@dispensaryAjax'])->name('dispensaryAjax');
		Route::get('/dispensaries/add', ['uses' => 'Backend\DispensaryController@create'])->name('createDispensary');
		Route::post('/dispensaries/checkDispensary/{id?}', ['uses' => 'Backend\DispensaryController@checkDispensary'])->name('checkDispensary');
		Route::post('/dispensaries/store', ['uses' => 'Backend\DispensaryController@store'])->name('addDispensary');
		Route::get('/dispensaries/view/{id?}', ['uses' => 'Backend\DispensaryController@show'])->name('viewDispensary');
		Route::get('/dispensaries/edit/{id?}', ['uses' => 'Backend\DispensaryController@edit'])->name('editDispensary');
		Route::post('/dispensaries/update/{id?}', ['uses' => 'Backend\DispensaryController@update'])->name('updateDispensary');
		Route::post('/dispensaries/update_status', ['uses' => 'Backend\DispensaryController@updateStatus'])->name('changeStatusDispensary');
		Route::post('/dispensaries/update_statusAjax', ['uses' => 'Backend\DispensaryController@updateStatusAjax'])->name('changeStatusAjaxDispensary');
		Route::post('/dispensaries/delete', ['uses' => 'Backend\DispensaryController@destroy'])->name('deleteDispensary');
		Route::post('/dispensaries/bulkdelete', ['uses' => 'Backend\DispensaryController@bulkdelete'])->name('deleteDispensaries');
		Route::post('/dispensaries/bulkupdate_status', ['uses' => 'Backend\DispensaryController@bulkchangeStatus'])->name('changeStatusDispensaries');
		Route::any('/dispensaries/import', ['uses' => 'Backend\DispensaryController@import'])->name('importDispensary');
		Route::get('/locationLogsAjax', ['uses' => 'Backend\DispensaryController@locationLogsAjax'])->name('locationLogsAjax');

		//Doctor Module
		Route::get('/doctors/list', ['uses' => 'Backend\DoctorController@index'])->name('doctors');
		Route::post('/doctorAjax', ['uses' => 'Backend\DoctorController@doctorAjax'])->name('doctorAjax');
		Route::get('/doctors/add', ['uses' => 'Backend\DoctorController@create'])->name('createDoctor');
		Route::post('/doctors/checkDoctor/{id?}', ['uses' => 'Backend\DoctorController@checkDoctor'])->name('checkDoctor');
		Route::post('/doctors/store', ['uses' => 'Backend\DoctorController@store'])->name('addDoctor');
		Route::get('/doctors/view/{id?}', ['uses' => 'Backend\DoctorController@show'])->name('viewDoctor');
		Route::get('/doctors/edit/{id?}', ['uses' => 'Backend\DoctorController@edit'])->name('editDoctor');
		Route::post('/doctors/update/{id?}', ['uses' => 'Backend\DoctorController@update'])->name('updateDoctor');
		Route::post('/doctors/update_status', ['uses' => 'Backend\DoctorController@updateStatus'])->name('changeStatusDoctor');
		Route::post('/doctors/update_statusAjax', ['uses' => 'Backend\DoctorController@updateStatusAjax'])->name('changeStatusAjaxDoctor');
		Route::post('/doctors/delete', ['uses' => 'Backend\DoctorController@destroy'])->name('deleteDoctor');
		Route::post('/doctors/bulkdelete', ['uses' => 'Backend\DoctorController@bulkdelete'])->name('deleteDoctors');
		Route::post('/doctors/bulkupdate_status', ['uses' => 'Backend\DoctorController@bulkchangeStatus'])->name('changeStatusDoctors');
		Route::any('/doctors/import', ['uses' => 'Backend\DoctorController@import'])->name('importDoctor');

		Route::get('/doctors/imageUpload', ['uses' => 'Backend\DoctorController@imageUpload'])->name('imageUploadDoctor');
        Route::post('/doctors/bulkImageUpload', ['uses' => 'Backend\DoctorController@bulkImageUpload'])->name('bulkImageUploadDoctor');
        Route::get('/doctorLogsAjax', ['uses' => 'Backend\DoctorController@doctorLogsAjax'])->name('doctorLogsAjax');

        Route::get('clears', ['uses' => 'Backend\DoctorController@clears'])->name('clears');
        Route::post('clears/record', ['uses' => 'Backend\DoctorController@clearRecord'])->name('clearRecord');





		//Brand Module
		Route::get('/brands/list', ['uses' => 'Backend\BrandController@index'])->name('brands');
		Route::post('/brandAjax', ['uses' => 'Backend\BrandController@brandAjax'])->name('brandAjax');
		Route::get('/brands/add', ['uses' => 'Backend\BrandController@create'])->name('createBrand');
		Route::post('/brands/checkBrand/{id?}', ['uses' => 'Backend\BrandController@checkBrand'])->name('checkBrand');
		Route::post('/brands/store', ['uses' => 'Backend\BrandController@store'])->name('addBrand');
		Route::get('/brands/view/{id?}', ['uses' => 'Backend\BrandController@show'])->name('viewBrand');
		Route::get('/brands/edit/{id?}', ['uses' => 'Backend\BrandController@edit'])->name('editBrand');
		Route::post('/brands/update/{id?}', ['uses' => 'Backend\BrandController@update'])->name('updateBrand');
		Route::post('/brands/update_status', ['uses' => 'Backend\BrandController@updateStatus'])->name('changeStatusBrand');
		Route::post('/brands/update_statusAjax', ['uses' => 'Backend\BrandController@updateStatusAjax'])->name('changeStatusAjaxBrand');
		Route::post('/brands/delete', ['uses' => 'Backend\BrandController@destroy'])->name('deleteBrand');
		Route::post('/brands/bulkdelete', ['uses' => 'Backend\BrandController@bulkdelete'])->name('deleteBrands');
		Route::post('/brands/bulkupdate_status', ['uses' => 'Backend\BrandController@bulkchangeStatus'])->name('changeStatusBrands');


		//State Module
		Route::get('/states/list', ['uses' => 'Backend\StateController@index'])->name('states');
		Route::post('/stateAjax', ['uses' => 'Backend\StateController@stateAjax'])->name('stateAjax');
		Route::get('/states/add', ['uses' => 'Backend\StateController@create'])->name('createState');
		Route::post('/states/checkState/{id?}', ['uses' => 'Backend\StateController@checkState'])->name('checkState');
		Route::post('/states/store', ['uses' => 'Backend\StateController@store'])->name('addState');
		Route::get('/states/view/{id?}', ['uses' => 'Backend\StateController@show'])->name('viewState');
		Route::get('/states/edit/{id?}', ['uses' => 'Backend\StateController@edit'])->name('editState');
		Route::post('/states/update/{id?}', ['uses' => 'Backend\StateController@update'])->name('updateState');
		Route::post('/states/update_status', ['uses' => 'Backend\StateController@updateStatus'])->name('changeStatusState');
		Route::post('/states/update_statusAjax', ['uses' => 'Backend\StateController@updateStatusAjax'])->name('changeStatusAjaxState');
		Route::post('/states/delete', ['uses' => 'Backend\StateController@destroy'])->name('deleteState');
		Route::post('/states/bulkdelete', ['uses' => 'Backend\StateController@bulkdelete'])->name('deleteStates');
		Route::post('/states/bulkupdate_status', ['uses' => 'Backend\StateController@bulkchangeStatus'])->name('changeStatusStates');


		//AllowState Module
		Route::get('/allowstates/list', ['uses' => 'Backend\AllowStateController@index'])->name('allowstates');
		Route::post('/allowstateAjax', ['uses' => 'Backend\AllowStateController@allowstateAjax'])->name('allowstateAjax');
		Route::get('/allowstates/add', ['uses' => 'Backend\AllowStateController@create'])->name('createAllowState');
		Route::post('/allowstates/checkAllowState/{id?}', ['uses' => 'Backend\AllowStateController@checkAllowState'])->name('checkAllowState');
		Route::post('/allowstates/store', ['uses' => 'Backend\AllowStateController@store'])->name('addAllowState');
		Route::get('/allowstates/view/{id?}', ['uses' => 'Backend\AllowStateController@show'])->name('viewAllowState');
		Route::get('/allowstates/edit/{id?}', ['uses' => 'Backend\AllowStateController@edit'])->name('editAllowState');
		Route::post('/allowstates/update/{id?}', ['uses' => 'Backend\AllowStateController@update'])->name('updateAllowState');
		Route::post('/allowstates/update_status', ['uses' => 'Backend\AllowStateController@updateStatus'])->name('changeStatusAllowState');
		Route::post('/allowstates/update_statusAjax', ['uses' => 'Backend\AllowStateController@updateStatusAjax'])->name('changeStatusAjaxAllowState');
		Route::post('/allowstates/delete', ['uses' => 'Backend\AllowStateController@destroy'])->name('deleteAllowState');
		Route::post('/allowstates/bulkdelete', ['uses' => 'Backend\AllowStateController@bulkdelete'])->name('deleteAllowStates');
		Route::post('/allowstates/bulkupdate_status', ['uses' => 'Backend\AllowStateController@bulkchangeStatus'])->name('changeStatusAllowStates');



		//Filemanager Module
		Route::get('/filemanagers/list', ['uses' => 'Backend\FilemanagerController@index'])->name('filemanagers');
		Route::post('/filemanagerAjax', ['uses' => 'Backend\FilemanagerController@filemanagerAjax'])->name('filemanagerAjax');
		Route::get('/filemanagers/add', ['uses' => 'Backend\FilemanagerController@create'])->name('createFilemanager');
		Route::post('/filemanagers/store', ['uses' => 'Backend\FilemanagerController@store'])->name('addFilemanager');
		Route::get('/filemanagers/view/{id?}', ['uses' => 'Backend\FilemanagerController@show'])->name('viewFilemanager');
		Route::get('/filemanagers/edit/{id?}', ['uses' => 'Backend\FilemanagerController@edit'])->name('editFilemanager');
		Route::post('/filemanagers/update/{id?}', ['uses' => 'Backend\FilemanagerController@update'])->name('updateFilemanager');
		Route::post('/filemanagers/delete', ['uses' => 'Backend\FilemanagerController@destroy'])->name('deleteFilemanager');
		Route::post('/filemanagers/bulkdelete', ['uses' => 'Backend\FilemanagerController@bulkdelete'])->name('deleteFilemanagers');
		


		//Strain Module 
		Route::get('/strains/list', ['uses' => 'Backend\StrainController@index'])->name('strains');
		Route::post('/strainAjax', ['uses' => 'Backend\StrainController@strainAjax'])->name('strainAjax');
		Route::get('/strains/add', ['uses' => 'Backend\StrainController@create'])->name('createStrain');
		Route::post('/strains/checkStrain/{id?}', ['uses' => 'Backend\StrainController@checkStrain'])->name('checkStrain');
		Route::post('/strains/store', ['uses' => 'Backend\StrainController@store'])->name('addStrain');
		Route::get('/strains/view/{id?}', ['uses' => 'Backend\StrainController@show'])->name('viewStrain');
		Route::get('/strains/edit/{id?}', ['uses' => 'Backend\StrainController@edit'])->name('editStrain');
		Route::post('/strains/update/{id?}', ['uses' => 'Backend\StrainController@update'])->name('updateStrain');
		Route::post('/strains/update_status', ['uses' => 'Backend\StrainController@updateStatus'])->name('changeStatusStrain');
		Route::post('/strains/update_statusAjax', ['uses' => 'Backend\StrainController@updateStatusAjax'])->name('changeStatusAjaxStrain');
		Route::post('/strains/delete', ['uses' => 'Backend\StrainController@destroy'])->name('deleteStrain');
		Route::post('/strains/bulkdelete', ['uses' => 'Backend\StrainController@bulkdelete'])->name('deleteStrains');
		Route::post('/strains/bulkupdate_status', ['uses' => 'Backend\StrainController@bulkchangeStatus'])->name('changeStatusStrains');

		Route::post('/strains/update_statusOrderAjax', ['uses' => 'Backend\StrainController@updateStatusOrderAjax'])->name('changeStatusOrderAjaxStrain');


		//Type Module 
		Route::get('/types/list', ['uses' => 'Backend\ProductTypeController@index'])->name('types');
		Route::post('/typeAjax', ['uses' => 'Backend\ProductTypeController@typeAjax'])->name('typeAjax');
		Route::get('/types/add', ['uses' => 'Backend\ProductTypeController@create'])->name('createType');
		Route::post('/types/checkType/{id?}', ['uses' => 'Backend\ProductTypeController@checkType'])->name('checkType');
		Route::post('/types/store', ['uses' => 'Backend\ProductTypeController@store'])->name('addType');
		Route::get('/types/view/{id?}', ['uses' => 'Backend\ProductTypeController@show'])->name('viewType');
		Route::get('/types/edit/{id?}', ['uses' => 'Backend\ProductTypeController@edit'])->name('editType');
		Route::post('/types/update/{id?}', ['uses' => 'Backend\ProductTypeController@update'])->name('updateType');
		Route::post('/types/update_status', ['uses' => 'Backend\ProductTypeController@updateStatus'])->name('changeStatusType');
		Route::post('/types/update_statusAjax', ['uses' => 'Backend\ProductTypeController@updateStatusAjax'])->name('changeStatusAjaxType');
		Route::post('/types/delete', ['uses' => 'Backend\ProductTypeController@destroy'])->name('deleteType');
		Route::post('/types/bulkdelete', ['uses' => 'Backend\ProductTypeController@bulkdelete'])->name('deleteTypes');
		Route::post('/types/bulkupdate_status', ['uses' => 'Backend\ProductTypeController@bulkchangeStatus'])->name('changeStatusTypes');
		Route::post('/types/update_statusOrderAjax', ['uses' => 'Backend\ProductTypeController@updateStatusOrderAjax'])->name('changeStatusOrderAjaxType');


		//pushs Module  
		Route::get('pushs/list', ['uses' => 'Backend\PushController@index'])->name('pushs');
		Route::post('pushsAjax', ['uses' => 'Backend\PushController@pushsAjax'])->name('pushsAjax');
		Route::post('pushs/store', ['uses' => 'Backend\PushController@store'])->name('addPushs');
		Route::get('pushs/view/{id?}', ['uses' => 'Backend\PushController@show'])->name('viewPushs');

		//Transactions Module  
		Route::get('transactions/list', ['uses' => 'Backend\TransactionController@index'])->name('transactions');
		Route::post('transactionsAjax', ['uses' => 'Backend\TransactionController@transactionsAjax'])->name('transactionsAjax');
		Route::get('transactions/view/{id?}', ['uses' => 'Backend\TransactionController@show'])->name('viewTransactions');

		//Plan Module  
		Route::get('/plan/list', ['uses' => 'Backend\PlanController@index'])->name('plan');
		Route::get('/planAjax', ['uses' => 'Backend\PlanController@planAjax'])->name('planAjax');
		Route::get('/plan/view/{id?}', ['uses' => 'Backend\PlanController@show'])->name('viewPlan');
		Route::get('/plan/edit/{id?}', ['uses' => 'Backend\PlanController@edit'])->name('editPlan');
		Route::post('/plan/update/{id?}', ['uses' => 'Backend\PlanController@update'])->name('updatePlan');


	});



	
});

Route::get('/subscription/create', ['as'=>'home', 'uses'=>'SubscriptionController@index'])->name('subscription.create');
Route::post('order-post', ['as'=>'order-post','uses'=>'SubscriptionController@orderPost']);

Route::get('stripe_success', ['as'=>'order-post','uses'=>'SubscriptionController@stripe_success'])->name('stripe_success'); 
Route::get('stripe_failed', ['as'=>'order-post','uses'=>'SubscriptionController@stripe_failed'])->name('stripe_failed'); 



Route::get('cron/job', 'HomeController@cronJob')->name('cronJob');
Route::get('cron/job/stock', 'HomeController@cronJobStock')->name('cronJobStock');
Route::get('cron/importLocation_new', 'Backend\CronController@importLocation')->name('importLocation');
Route::get('cron/importProduct_new', 'Backend\CronController@importProduct')->name('importProduct');

Route::get('{slug}', 'HomeController@getPage')->name('getPage');